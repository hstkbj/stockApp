<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mouvement;
use App\Models\Product;

class MouvementController extends Controller
{
    public function index(){
        $query = Mouvement::with('product', 'user');
        $mouvements = $query->latest()->get();
        return response()->json($mouvements);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantite'   => 'required|integer|min:1',
            'type'       => 'required|in:entree,sortie',
            'date'       => 'required|date',
            'motif'      => 'nullable|string|max:255',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        // Vérification stock suffisant pour une sortie
        if ($validated['type'] === 'sortie' && $product->quantite < $validated['quantite']) {
            return response()->json([
                'message' => 'Stock insuffisant. Disponible : ' . $product->quantite
            ], 422);
        }

        // Mise à jour du stock
        if ($validated['type'] === 'entree') {
            $product->increment('quantite', $validated['quantite']);
        } else {
            $product->decrement('quantite', $validated['quantite']);
        }

        $validated['user_id'] = auth()->id();

        $mouvement = Mouvement::create($validated);
        $mouvement->load('product', 'user');

        return response()->json($mouvement, 200);
    }

    public function show(Mouvement $mouvement)
    {
        $mouvement->load('product', 'user');
        return response()->json($mouvement);
    }

    public function destroy(Mouvement $mouvement)
    {
        // Annuler l'effet du mouvement sur le stock
        $product = $mouvement->product;

        if ($mouvement->type === 'entree') {
            if ($product->quantite < $mouvement->quantite) {
                return response()->json([
                    'message' => 'Impossible d\'annuler : le stock actuel est insuffisant.'
                ], 422);
            }
            $product->decrement('quantite', $mouvement->quantite);
        } else {
            $product->increment('quantite', $mouvement->quantite);
        }

        $mouvement->delete();
        return response()->json(['message' => 'Mouvement supprimé et stock ajusté.']);
    }
}
