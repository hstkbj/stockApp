<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mouvement;
use App\Models\Product;
use App\Models\Vente;
use App\Models\VenteItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VenteController extends Controller
{
     public function index(){
        $query = Vente::with('user', 'items.product');
        $ventes = $query->latest()->get();
        return response()->json($ventes);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'montant_paye'        => 'required|numeric|min:0',
            'date'                => 'required|date',
            'items'               => 'required|array|min:1',
            'items.*.product_id'  => 'required|exists:products,id',
            'items.*.quantite'    => 'required|integer|min:1',
            'items.*.prix_unitaire' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $montant_total = 0;

            // Calcul du montant total & vérification stocks
            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);

                if ($product->quantite < $item['quantite']) {
                    DB::rollBack();
                    return response()->json([
                        'message' => "Stock insuffisant pour le produit : {$product->nom}. Disponible : {$product->quantite}"
                    ], 422);
                }

                $montant_total += $item['quantite'] * $item['prix_unitaire'];
            }

            $monnaie = max(0, $validated['montant_paye'] - $montant_total);

            // Création de la vente
            $vente = Vente::create([
                'reference'     => 'VNT-' . strtoupper(Str::random(8)),
                'montant_total' => $montant_total,
                'montant_paye'  => $validated['montant_paye'],
                'monnaie'       => $monnaie,
                'status'        => $validated['montant_paye'] >= $montant_total ? 'paye' : 'non_paye',
                'date'          => $validated['date'],
                'user_id'       => auth()->id(),
            ]);

            // Création des lignes et mise à jour du stock
            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $prix_total = $item['quantite'] * $item['prix_unitaire'];

                VenteItem::create([
                    'vente_id'      => $vente->id,
                    'product_id'    => $item['product_id'],
                    'quantite'      => $item['quantite'],
                    'prix_unitaire' => $item['prix_unitaire'],
                    'prix_total'    => $prix_total,
                ]);

                // Décrémenter le stock
                $product->decrement('quantite', $item['quantite']);

                // Enregistrer le mouvement
                Mouvement::create([
                    'product_id' => $item['product_id'],
                    'quantite'   => $item['quantite'],
                    'type'       => 'sortie',
                    'date'       => $validated['date'],
                    'motif'      => 'Vente ' . $vente->reference,
                    'user_id'    => auth()->id(),
                ]);
            }

            DB::commit();

            $vente->load('items.product', 'user');
            return response()->json($vente, 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erreur lors de la vente : ' . $e->getMessage()], 500);
        }
    }

    public function show(Vente $vente)
    {
        $vente->load('items.product', 'user');
        return response()->json($vente);
    }

    public function destroy(Vente $vente)
    {
        if (auth()->user()?->isMagasinier()) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        DB::beginTransaction();

        try {
            // Remettre le stock
            foreach ($vente->items as $item) {
                $item->product->increment('quantite', $item->quantite);
            }

            // Supprimer les mouvements liés
            Mouvement::where('motif', 'Vente ' . $vente->reference)->delete();

            $vente->items()->delete();
            $vente->delete();

            DB::commit();
            return response()->json(['message' => 'Vente annulée et stock restauré.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erreur : ' . $e->getMessage()], 500);
        }
    }
}
