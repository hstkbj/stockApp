<?php

namespace App\Http\Controllers;

use App\Models\Emplacement;
use App\Models\Invoice;
use App\Models\Mouvement;
use App\Models\InvoiceItem;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Mail\InvoiceMail;
use Illuminate\Support\Facades\Mail;

class InvoiceController extends Controller
{
    const TVA_BENIN = 18; // 18%

    public function index(Request $request){

        $request->validate([
            'emplacement' => 'required|in:boutique,magasin',
        ]);

        $emplacement = Emplacement::where('nom', $request->emplacement)->firstOrFail();

        $invoices = Invoice::with(['client', 'user', 'emplacement', 'items.product'])
            ->where('emplacement_id', $emplacement->id)
            ->latest()
            ->get();

        return response()->json($invoices);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id'          => 'nullable|exists:clients,id',
            'emplacement_id'     => 'required|exists:emplacements,id',
            'due_at'             => 'required|date',
            'echeance_at'        => 'required|date',
            'is_taxable'         => 'required|boolean', // true = 18% / false = exonéré
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $invoice = DB::transaction(function () use ($validated) {
            // Calcul des totaux
            $totalHt  = collect($validated['items'])->sum(
                fn($item) => $item['quantity'] * $item['unit_price']
            );
            $tauxTva  = $validated['is_taxable'] ? self::TVA_BENIN / 100 : 0;
            $totalTva = round($totalHt * $tauxTva, 2);
            $totalTtc = round($totalHt + $totalTva, 2);

            // Client anonyme si aucun client sélectionné
            $anonymousName = null;
            if (empty($validated['client_id'])) {
                $anonymousName = 'Client-' . strtoupper(Str::random(6));
            }

            $invoice = Invoice::create([
                'invoice_number'          => 'INV-' . strtoupper(Str::random(8)) . '-' . now()->format('Ymd'),
                'status'                  => 'draft', // toujours draft à la création
                'due_at'                  => $validated['due_at'],
                'echeance_at'             => $validated['echeance_at'],
                'total_ht'                => $totalHt,
                'total_tva'               => $totalTva,
                'total_ttc'               => $totalTtc,
                'client_id'               => $validated['client_id'] ?? null,
                'anonymous_customer_name' => $anonymousName,
                'emplacement_id'          => $validated['emplacement_id'],
                'user_id'                 => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                InvoiceItem::create([
                    'invoice_id'  => $invoice->id,
                    'product_id'  => $item['product_id'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'vat_rate'    => $validated['is_taxable'] ? self::TVA_BENIN : 0,
                ]);
            }

            // Vérifier le stock puis déduire immédiatement
            $this->verifierStockSuffisant($invoice);
            $this->deduireStock($invoice);

            // Enregistrer les mouvements de sortie pour chaque article
            foreach ($invoice->items as $item) {
                Mouvement::create([
                    'product_id'    => $item->product_id,
                    'emplacement_id'=> $invoice->emplacement_id,
                    'quantite'      => $item->quantity,
                    'type'          => 'sortie',
                    'date'          => now()->toDateString(),
                    'motif'         => 'Vente ' . $invoice->invoice_number,
                    'user_id'       => auth()->id(),
                ]);
            }

            return $invoice;
        });

        $invoice->load(['client', 'user', 'emplacement', 'items.product']);

        return response()->json($invoice, 201);
    }

    public function show(Invoice $invoice)
    {
        $invoice->load([
            'client', 
            'user', 
            'mecef' => fn($query) => $query->latest(),
            'emplacement', 
            'items.product'
            ]);

        return response()->json($invoice);
    }

    public function update(Request $request, Invoice $invoice)
    {
        if ($invoice->status === 'cancelled') {
            return response()->json([
                'message' => 'Une facture annulée ne peut pas être modifiée.',
            ], 422);
        }

        $validated = $request->validate([
            'client_id'          => 'nullable|exists:clients,id',
            'emplacement_id'     => 'required|exists:emplacements,id',
            'due_at'             => 'required|date',
            'echeance_at'        => 'required|date',
            'is_taxable'         => 'required|boolean',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($invoice, $validated) {

            // 0. Quantités par produit AVANT modification (regroupées, au cas où un produit apparaîtrait sur plusieurs lignes)
            $ancienQuantites = $invoice->items()->get()
                ->groupBy('product_id')
                ->map(fn($items) => $items->sum('quantity'));

            // 1. Remettre l'ancien stock avant modification
            $this->remettreStock($invoice);

            // 2. Recalcul des totaux
            $totalHt  = collect($validated['items'])->sum(
                fn($item) => $item['quantity'] * $item['unit_price']
            );
            $tauxTva  = $validated['is_taxable'] ? self::TVA_BENIN / 100 : 0;
            $totalTva = round($totalHt * $tauxTva, 2);
            $totalTtc = round($totalHt + $totalTva, 2);

            $anonymousName = $invoice->anonymous_customer_name;
            if (! empty($validated['client_id'])) {
                $anonymousName = null;
            } elseif (empty($validated['client_id']) && empty($invoice->client_id)) {
                $anonymousName = $anonymousName ?? 'Client-' . strtoupper(Str::random(6));
            }

            $invoice->update([
                'client_id'               => $validated['client_id'] ?? null,
                'anonymous_customer_name' => $anonymousName,
                'emplacement_id'          => $validated['emplacement_id'],
                'due_at'                  => $validated['due_at'],
                'echeance_at'             => $validated['echeance_at'],
                'total_ht'                => $totalHt,
                'total_tva'               => $totalTva,
                'total_ttc'               => $totalTtc,
            ]);

            // 3. Remplacer les lignes
            $invoice->items()->delete();
            foreach ($validated['items'] as $item) {
                InvoiceItem::create([
                    'invoice_id'  => $invoice->id,
                    'product_id'  => $item['product_id'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'vat_rate'    => $validated['is_taxable'] ? self::TVA_BENIN : 0,
                ]);
            }

            // 4. Vérifier et déduire le nouveau stock
            $invoice->load('items.product');
            $this->verifierStockSuffisant($invoice);
            $this->deduireStock($invoice);

            // 5. Quantités par produit APRÈS modification
            $nouvellesQuantites = $invoice->items
                ->groupBy('product_id')
                ->map(fn($items) => $items->sum('quantity'));

            // 6. Comparer et ne tracer QUE les écarts réels
            $tousLesProduits = $ancienQuantites->keys()
                ->merge($nouvellesQuantites->keys())
                ->unique();

            foreach ($tousLesProduits as $productId) {
                $avant = $ancienQuantites->get($productId, 0);
                $apres = $nouvellesQuantites->get($productId, 0);
                $ecart = $apres - $avant;

                if ($ecart === 0) {
                    continue; // rien n'a changé pour ce produit, pas de mouvement
                }

                Mouvement::create([
                    'product_id'     => $productId,
                    'emplacement_id' => $invoice->emplacement_id,
                    'quantite'       => abs($ecart),
                    'type'           => $ecart > 0 ? 'sortie' : 'entree',
                    'date'           => now()->toDateString(),
                    'motif'          => 'Ajustement modification facture ' . $invoice->invoice_number,
                    'user_id'        => auth()->id(),
                ]);
            }
        });

        $invoice->load(['client', 'user', 'emplacement', 'items.product']);

        return response()->json($invoice);
    }

    public function updateStatus(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'status' => 'required|in:draft,sent,paid,overdue,cancelled',
        ]);

        $ancienStatus  = $invoice->status;
        $nouveauStatus = $validated['status'];

        if ($ancienStatus === $nouveauStatus) {
            return response()->json($invoice);
        }

        if ($ancienStatus === 'cancelled') {
            return response()->json([
                'message' => 'Une facture annulée ne peut plus changer de statut.',
            ], 422);
        }

        DB::transaction(function () use ($invoice, $ancienStatus, $nouveauStatus) {
            if ($nouveauStatus === 'cancelled' && $ancienStatus !== 'cancelled') {
                $this->remettreStock($invoice);
            }

            $invoice->update(['status' => $nouveauStatus]);
        });

        $invoice->load(['client', 'user', 'emplacement', 'items.product']);

        return response()->json($invoice);
    }

    public function cancel(Invoice $invoice)
    {
        if ($invoice->status === 'cancelled') {
            return response()->json(['message' => 'Facture déjà annulée.'], 422);
        }

        DB::transaction(function () use ($invoice) {
            $this->remettreStock($invoice);
            $invoice->update(['status' => 'cancelled']);
        });

        $invoice->load(['client', 'user', 'emplacement', 'items.product']);

        return response()->json($invoice);
    }

    public function destroy(Invoice $invoice)
    {
        DB::transaction(function () use ($invoice) {
            // Si déjà annulée, le stock a déjà été remis via cancel()
            if ($invoice->status !== 'cancelled') {
                $invoice->load('items');
                $this->remettreStock($invoice);

                foreach ($invoice->items as $item) {
                    Mouvement::create([
                        'product_id'     => $item->product_id,
                        'emplacement_id' => $invoice->emplacement_id,
                        'quantite'       => $item->quantity,
                        'type'           => 'entree',
                        'date'           => now()->toDateString(),
                        'motif'          => 'Suppression facture ' . $invoice->invoice_number,
                        'user_id'        => auth()->id(),
                    ]);
                }
            }

            $invoice->delete(); // items supprimés par cascade
        });

        return response()->json(['message' => 'Facture supprimée avec succès.']);
    }

    public function downloadPdf(Invoice $invoice){
        $invoice->load([
            'client',
            'user',
            'emplacement',
            'items.product',
            'mecef' => fn($q) => $q->latest(),
        ]);

        // Générer le QR Code en base64 si la facture est normalisée
        $qrCodeBase64 = null;
        $mecef = $invoice->mecef->first();

        if ($mecef?->qr_code) {
            $qrCodeBase64 = base64_encode(
                QrCode::format('svg')->size(120)->generate($mecef->qr_code)
            );
        }

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice'      => $invoice,
            'mecef'        => $mecef,
            'qrCodeBase64' => $qrCodeBase64,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('facture-' . $invoice->invoice_number . '.pdf');
    }

    public function sendByEmail(Invoice $invoice)
    {
        $invoice->load([
            'client',
            'user',
            'emplacement',
            'items.product',
            'mecef' => fn($q) => $q->latest(),
        ]);

        // Vérifier si la facture a un client avec un email
        if (! $invoice->client) {
            return response()->json([
                'message' => 'Cette facture est associée à un client anonyme — aucun email à envoyer.',
            ], 422);
        }

        if (! $invoice->client->email) {
            return response()->json([
                'message' => "Le client \"{$invoice->client->fullname}\" n'a pas d'adresse email enregistrée.",
            ], 422);
        }

        // Générer le QR Code SVG si normalisée
        $mecef     = $invoice->mecef->first();
        $qrCodeSvg = null;

        if ($mecef?->qr_code) {
            $qrCodeSvg = QrCode::format('svg')->size(120)->generate($mecef->qr_code);
        }

        // 1. Générer le PDF
        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice'   => $invoice,
            'mecef'     => $mecef,
            'qrCodeBase64' => $qrCodeSvg,
        ])->setPaper('a4', 'portrait');

        // 2. Sauvegarder temporairement dans storage/app/temp/
        $fileName  = 'facture-' . $invoice->invoice_number . '-' . time() . '.pdf';
        $tempPath  = storage_path('app/temp/' . $fileName);

        // Créer le dossier temp s'il n'existe pas
        if (! file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $pdf->save($tempPath);

        // 3. Envoyer le mail avec le PDF en pièce jointe
        try {
            Mail::to($invoice->client->email)
                ->send(new InvoiceMail($invoice, $tempPath));

        } catch (\Exception $e) {
            // Supprimer le fichier même si l'envoi échoue
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }

            return response()->json([
                'message' => "Erreur lors de l'envoi du mail : " . $e->getMessage(),
            ], 500);
        }

        // 4. Supprimer le fichier temporaire après envoi
        if (file_exists($tempPath)) {
            unlink($tempPath);
        }

        return response()->json([
            'message' => "Facture envoyée avec succès à {$invoice->client->email}.",
        ]);
    }

    // -------------------------------------------------------------------------
    // Helpers privés
    // -------------------------------------------------------------------------

    private function verifierStockSuffisant(Invoice $invoice): void
    {
        $invoice->load('items.product');

        foreach ($invoice->items as $item) {
            $stock      = Stock::where('product_id', $item->product_id)
                ->where('emplacement_id', $invoice->emplacement_id)
                ->first();

            $disponible = $stock?->quantite ?? 0;

            if ($disponible < $item->quantity) {
                abort(422, "Stock insuffisant pour \"{$item->product->nom}\". Disponible : {$disponible}, demandé : {$item->quantity}.");
            }
        }
    }

    private function deduireStock(Invoice $invoice): void
    {
        $invoice->load('items');

        foreach ($invoice->items as $item) {
            Stock::where('product_id', $item->product_id)
                ->where('emplacement_id', $invoice->emplacement_id)
                ->decrement('quantite', $item->quantity);
        }
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
