<?php

namespace App\Http\Controllers;

use App\Models\Aprovisionnement;
use App\Models\AprovisionnementItem;
use App\Models\Mouvement;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Emplacement;
use App\Models\Stock;
use App\Mail\BonDeCommandeMail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;

class AprovisionnementController extends Controller
{
    public function index(Request $request){

        $request->validate([
            'emplacement' => 'required|in:boutique,magasin',
        ]);

        $emplacement = Emplacement::where('nom', $request->emplacement)->firstOrFail();

        $query = Aprovisionnement::with('fournisseur', 'user', 'items.product');
        $approvisionnements = $query->where('emplacement_id', $emplacement->id)->latest()->get();
        return response()->json($approvisionnements);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fournisseur_id'          => 'required|exists:fournisseurs,id',
            'date_approvisionnement'  => 'required|date',
            'emplacement'             => 'required|in:boutique,magasin',
            'items'                   => 'required|array|min:1',
            'items.*.product_id'      => 'required|exists:products,id',
            'items.*.quantite'        => 'required|integer|min:1',
            'items.*.prix_unitaire'   => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {

            $montant_total = 0;

            $emplacement = Emplacement::where('nom', $request->emplacement)->firstOrFail();

            foreach ($validated['items'] as $item) {
                $montant_total += $item['quantite'] * $item['prix_unitaire'];
            }

            // Création approvisionnement
            $appro = Aprovisionnement::create([
                'reference'              => 'APR-' . strtoupper(Str::random(8)),
                'emplacement_id'         => $emplacement->id,
                'fournisseur_id'         => $validated['fournisseur_id'],
                'montant_total'          => $montant_total,
                'date_approvisionnement' => $validated['date_approvisionnement'],
                'user_id'                => auth()->id(),
                'status'                 => 'brouillon',
            ]);

            // Création des items uniquement
            foreach ($validated['items'] as $item) {

                $prix_total = $item['quantite'] * $item['prix_unitaire'];

                AprovisionnementItem::create([
                    'aprovisionnement_id' => $appro->id,
                    'product_id'          => $item['product_id'],
                    'quantite'            => $item['quantite'],
                    'prix_unitaire'       => $item['prix_unitaire'],
                    'prix_total'          => $prix_total,
                ]);
            }

            DB::commit();

            $appro->load('fournisseur', 'items.product', 'user');

            return response()->json($appro, 201);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Aprovisionnement $aprovisionnement)
    {
        $aprovisionnement->load('fournisseur', 'items.product', 'user');
        return response()->json($aprovisionnement);
    }

    public function destroy(Aprovisionnement $aprovisionnement)
    {
        DB::beginTransaction();

        try {
            // On n'ajuste le stock QUE si l'appro avait été livré (donc avait impacté le stock)
            if ($aprovisionnement->status === 'livrer') {
                foreach ($aprovisionnement->items as $item) {
                    $product = $item->product;

                    if ($product->quantite < $item->quantite) {
                        DB::rollBack();
                        return response()->json([
                            'message' => "Stock insuffisant pour annuler l'approvisionnement du produit : {$product->nom}"
                        ], 422);
                    }

                    $product->decrement('quantite', $item->quantite);
                }

                // Décrémenter aussi le Stock par emplacement (celui utilisé dans livrer())
                foreach ($aprovisionnement->items as $item) {
                    Stock::where('product_id', $item->product_id)
                        ->where('emplacement_id', $aprovisionnement->emplacement_id)
                        ->decrement('quantite', $item->quantite);
                }

                Mouvement::where('motif', 'Approvisionnement ' . $aprovisionnement->reference)->delete();
            }

            $aprovisionnement->items()->delete();
            $aprovisionnement->delete();

            DB::commit();
            return response()->json(['message' => 'Approvisionnement annulé' . ($aprovisionnement->status === 'livrer' ? ' et stock ajusté.' : '.')]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erreur : ' . $e->getMessage()], 500);
        }
    }

    public function enAttente(Request $request, Aprovisionnement $aprovisionnement){

        if ($aprovisionnement->status === 'enAttente') {
            return response()->json([
                'message' => 'Cet approvisionnement est déjà en attente.'
            ], 422);
        }

        $validated = $request->validate([
            'send_email' => 'nullable|boolean', // true = envoyer le bon de commande
        ]);

        // Charger les items et l'emplacement de l'approvisionnement
        $aprovisionnement->load('items.product','fournisseur');

        $aprovisionnement->update(['status' => 'enAttente']);

        // Envoyer le mail seulement si send_email = true ET que le fournisseur a un email
        if (! empty($validated['send_email']) && $aprovisionnement->fournisseur?->email) {

            // 1. Générer le PDF
            $pdf = Pdf::loadView('pdf.bon-de-commande', [
                'aprovisionnement' => $aprovisionnement,
            ])->setPaper('a4', 'portrait');

            // 2. Sauvegarder temporairement
            $fileName = 'bdc-' . $aprovisionnement->reference . '-' . time() . '.pdf';
            $tempPath = storage_path('app/temp/' . $fileName);

            if (! file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }

            $pdf->save($tempPath);

            // 3. Envoyer le mail
            try {
                Mail::to($aprovisionnement->fournisseur->email)
                    ->send(new BonDeCommandeMail($aprovisionnement, $tempPath));

                $emailEnvoye = true;

            } catch (\Exception $e) {
                if (file_exists($tempPath)) unlink($tempPath);

                return response()->json([
                    'message' => "Approvisionnement mis en attente mais erreur d'envoi : " . $e->getMessage(),
                ], 500);
            }

            // 4. Supprimer le fichier temporaire
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
        }

        return response()->json([
            'message' => 'Approvisionnement mis en attente.'
                . (! empty($validated['send_email']) && $aprovisionnement->fournisseur?->email
                    ? ' Bon de commande envoyé au fournisseur.'
                    : ''),
        ]);

    }

    public function livrer(Aprovisionnement $aprovisionnement)
    {
        if ($aprovisionnement->status === 'livrer') {
            return response()->json([
                'message' => 'Cet approvisionnement est déjà livré.'
            ], 422);
        }

        // Charger les items et l'emplacement de l'approvisionnement
        $aprovisionnement->load('items');

        DB::beginTransaction();

        try {
            foreach ($aprovisionnement->items as $item) {

                // Trouver le stock du produit dans l'emplacement de l'approvisionnement
                $stock = Stock::where('product_id', $item->product_id)
                    ->where('emplacement_id', $aprovisionnement->emplacement_id)
                    ->first();

                if (! $stock) {
                    // Si le stock n'existe pas encore pour cet emplacement, le créer
                    $stock = Stock::create([
                        'product_id'     => $item->product_id,
                        'emplacement_id' => $aprovisionnement->emplacement_id,
                        'quantite'       => 0,
                        'seuil_alerte'   => 0,
                    ]);
                }

                // Incrémenter le stock de l'emplacement concerné
                $stock->increment('quantite', $item->quantite);

                // Mouvement d'entrée lié à l'emplacement
                Mouvement::create([
                    'product_id'    => $item->product_id,
                    'emplacement_id'=> $aprovisionnement->emplacement_id,
                    'quantite'      => $item->quantite,
                    'type'          => 'entree',
                    'date'          => now()->toDateString(),
                    'motif'         => 'Approvisionnement ' . $aprovisionnement->reference,
                    'user_id'       => auth()->id(),
                ]);
            }

            $aprovisionnement->update(['status' => 'livrer']);

            DB::commit();

            return response()->json([
                'message' => 'Approvisionnement livré avec succès.'
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
