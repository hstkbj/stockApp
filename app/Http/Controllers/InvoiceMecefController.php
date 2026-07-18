<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceMecef;
use App\Models\Stock;
use Illuminate\Http\Request;
use App\Services\MecefService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InvoiceMecefController extends Controller
{

    public function normalizeInvoice(Request $request, Invoice $invoice){

        $validated = $request->validate([
            'payment_type' => 'required|in:ESPECES,VIREMENT,CARTEBANCAIRE,MOBILEMONEY,CHEQUES,CREDIT,AUTRE',
            'invoice_type' => 'nullable|in:FV,FA,EV,EA',
        ]);

        // Bloquer si déjà normalisée
        if ($invoice->mecef?->status === 'confirmed') {
            return response()->json([
                'message' => 'Cette facture est déjà normalisée.',
            ], 422);
        }

        if ($invoice->status !== 'paid') {
            return response()->json([
                'message' => 'Seule les factures payé peuvent être normalisée.',
            ], 422);
        }

        // Charger les relations nécessaires
        $invoice->load(['user', 'client', 'items.product']);

        $mecef        = new MecefService();
        $invoiceType  = $validated['invoice_type'] ?? 'FV';
        $paymentType  = $validated['payment_type'];

        // Étape 1 : Envoyer la facture
        $response = $mecef->sendInvoice($invoice, $invoiceType, $paymentType);

        if (isset($response['errorCode'])) {
            return response()->json([
                'message'   => $response['errorDesc'],
                'errorCode' => $response['errorCode'],
            ], 422);
        }

        $uid = $response['uid']
                ?? $response['data']['uid']
                ?? $response['result']['uid']
                ?? null;

        if (!$uid) {
            return response()->json([
                'message' => 'UID introuvable dans la réponse MECeF',
                'response' => $response
            ], 422);
        }

        // ── Vérification des totaux ──────────────────────────────────────────────────
        // L'API retourne les montants en entiers (centimes ou francs entiers pour le FCFA)
        // On compare avec notre total_ttc arrondi
        $totalMecef   = (int) $response['total'];
        $totalFacture = (int) round($invoice->total_ttc);

        if ($totalMecef !== $totalFacture) {
            // Les totaux ne correspondent pas → on annule la demande en attente côté DGI
            // pour ne pas bloquer les 10 slots de l'e-MCF
            $mecef->cancelInvoice($uid);

            return response()->json([
                'message' => 'Les totaux ne correspondent pas. Facture annulée côté MECeF.',
                'details' => [
                    'total_notre_systeme' => $totalFacture,
                    'total_mecef'         => $totalMecef,
                    'difference'          => abs($totalMecef - $totalFacture),
                ],
            ], 422);
        }

        // Étape 2 : Confirmer la facture
        $confirmation = $mecef->confirmInvoice($uid);

        if (isset($confirmation['errorCode'])) {
            return response()->json([
                'message'   => $confirmation['errorDesc'],
                'errorCode' => $confirmation['errorCode'],
            ], 422);
        }

        // Étape 3 : Sauvegarder
        $data = DB::transaction(function () use ($invoice, $uid, $invoiceType, $paymentType, $response, $confirmation) {
            return InvoiceMecef::create([
                'invoice_id'     => $invoice->id,
                'uid'            => $uid,
                'invoice_type'   => $invoiceType,
                'payment_type'   => $paymentType,
                'code_mecef_dgi' => $confirmation['codeMECeFDGI'],
                'qr_code'        => $confirmation['qrCode'],
                'nim'            => $confirmation['nim'],
                'counters'       => $confirmation['counters'],
                'mecef_datetime' => Carbon::createFromFormat('d/m/Y H:i:s', $confirmation['dateTime']),
                'total_mecef'    => $response['total'],
                'vat_b'          => $response['vab'],
                'ht_b'           => $response['hab'],
                'status'         => 'confirmed',
            ]);
        });

        return response()->json($data, 201);
    }

    public function cancelNormalizedInvoice(Request $request, Invoice $invoice)
    {
        // Récupère le dernier enregistrement MECeF confirmé pour cette facture
        $confirmedMecef = $invoice->mecef()
            ->where('status', 'confirmed')
            ->latest()
            ->first();

        if (!$confirmedMecef) {
            return response()->json([
                'message' => 'Cette facture n\'est pas normalisée.',
            ], 422);
        }

        $paymentType = $request->payment_type ?? 'ESPECES';

        $mecef = new MecefService();

        // Étape 1 : création de l'avoir
        $response = $mecef->sendAvoirCancelledInvoice(
            $invoice,
            'FA',
            $paymentType
        );

        if (isset($response['errorCode'])) {
            return response()->json([
                'message'   => $response['errorDesc'],
                'errorCode' => $response['errorCode']
            ], 422);
        }

        $uid = $response['uid']
                ?? $response['data']['uid']
                ?? $response['result']['uid']
                ?? null;

        if (!$uid) {
            return response()->json([
                'message' => 'UID introuvable.'
            ], 422);
        }

        // Étape 2 : confirmation de l'avoir
        $confirmation = $mecef->confirmInvoice($uid);

        if (isset($confirmation['errorCode'])) {
            return response()->json([
                'message'   => $confirmation['errorDesc'],
                'errorCode' => $confirmation['errorCode']
            ], 422);
        }

        // Étape 3 : sauvegarde + remise en stock + annulation de la facture
        $avoir = DB::transaction(function () use ($invoice, $uid, $paymentType, $response, $confirmation) {
            $avoir = InvoiceMecef::create([
                'invoice_id'     => $invoice->id,
                'uid'            => $uid,
                'invoice_type'   => 'FA',
                'payment_type'   => $paymentType,
                'code_mecef_dgi' => $confirmation['codeMECeFDGI'] ?? null,
                'qr_code'        => $confirmation['qrCode'] ?? null,
                'nim'            => $confirmation['nim'] ?? null,
                'counters'       => $confirmation['counters'] ?? null,
                'mecef_datetime' => isset($confirmation['dateTime'])
                    ? Carbon::createFromFormat('d/m/Y H:i:s', $confirmation['dateTime'])
                    : now(),
                'total_mecef'    => $response['total'] ?? null,
                'vat_b'          => $response['vab'] ?? null,
                'ht_b'           => $response['hab'] ?? null,
                'status'         => 'cancelled',
            ]);

            // La facture d'avoir e-MCF annule fiscalement la facture d'origine :
            // on remet donc les quantités vendues dans le stock...
            $this->remettreStock($invoice);

            // ...et on marque la facture comme annulée pour refléter son état réel.
            $invoice->update(['status' => 'cancelled']);

            return $avoir;
        });

        return response()->json([
            'message' => 'Facture annulée avec succès.',
            'data'    => $avoir
        ]);
    }

    private function remettreStock(Invoice $invoice): void
    {
        $invoice->load('items');

        foreach ($invoice->items as $item) {
            Stock::where('product_id', $item->product_id)
                ->where('emplacement_id', $invoice->emplacement_id)
                ->increment('quantite', $item->quantity);
        }
    }

}
