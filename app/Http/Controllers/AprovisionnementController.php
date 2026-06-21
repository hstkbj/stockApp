<?php

namespace App\Http\Controllers;

use App\Models\Aprovisionnement;
use App\Models\AprovisionnementItem;
use App\Models\Mouvement;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AprovisionnementController extends Controller
{
    public function index(){
        $query = Aprovisionnement::with('fournisseur', 'user', 'items.product');
        $approvisionnements = $query->latest()->get();
        return response()->json($approvisionnements);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fournisseur_id'          => 'required|exists:fournisseurs,id',
            'date_approvisionnement'  => 'required|date',
            'items'                   => 'required|array|min:1',
            'items.*.product_id'      => 'required|exists:products,id',
            'items.*.quantite'        => 'required|integer|min:1',
            'items.*.prix_unitaire'   => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {

            $montant_total = 0;

            foreach ($validated['items'] as $item) {
                $montant_total += $item['quantite'] * $item['prix_unitaire'];
            }

            // Création approvisionnement
            $appro = Aprovisionnement::create([
                'reference'              => 'APR-' . strtoupper(Str::random(8)),
                'fournisseur_id'         => $validated['fournisseur_id'],
                'montant_total'          => $montant_total,
                'date_approvisionnement' => $validated['date_approvisionnement'],
                'user_id'                => auth()->id(),
                'status'                 => 'enAttente',
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
            // Annuler l'effet sur le stock
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

            // Supprimer les mouvements liés
            Mouvement::where('motif', 'Approvisionnement ' . $aprovisionnement->reference)->delete();

            $aprovisionnement->items()->delete();
            $aprovisionnement->delete();

            DB::commit();
            return response()->json(['message' => 'Approvisionnement annulé et stock ajusté.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erreur : ' . $e->getMessage()], 500);
        }
    }

    public function livrer(Aprovisionnement $aprovisionnement)
    {
        if ($aprovisionnement->status === 'livrer') {
            return response()->json([
                'message' => 'Cet approvisionnement est déjà livré.'
            ], 422);
        }

        DB::beginTransaction();

        try {

            foreach ($aprovisionnement->items as $item) {

                $product = Product::findOrFail($item->product_id);

                // Ajouter le stock
                $product->increment('quantite', $item->quantite);

                // Mouvement entrée
                Mouvement::create([
                    'product_id' => $item->product_id,
                    'quantite'   => $item->quantite,
                    'type'       => 'entree',
                    'date'       => now(),
                    'motif'      => 'Approvisionnement ' . $aprovisionnement->reference,
                    'user_id'    => auth()->id(),
                ]);
            }

            // Changer le statut
            $aprovisionnement->update([
                'status' => 'livrer'
            ]);

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
