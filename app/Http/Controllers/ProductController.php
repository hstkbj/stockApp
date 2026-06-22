<?php

namespace App\Http\Controllers;

use App\Models\Emplacement;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Mouvement;
use App\Models\Stock;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request){
        $query = Product::with('rayon.category', 'user','fournisseur', 'stocks.emplacement');
        $products = $query->latest()->get();
        return response()->json($products);
    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'code_barre'      => 'required|string|unique:products,code_barre',
            'nom'             => 'required|string|max:255',
            'prix_unitaire'   => 'required|numeric|min:0',
            'prix_achat'      => 'required|numeric|min:0',
            'seuil_alerte'    => 'required|integer|min:0',
            'fournisseur_id'  => 'required|exists:fournisseurs,id',
            'date_expiration' => 'nullable|date',
            'image'           => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'rayon_id'        => 'required|exists:rayons,id',
        ]);

        // Upload image
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        // seuil_alerte appartient à `stocks`, pas à `products`
        $seuilAlerte = $validated['seuil_alerte'];
        unset($validated['seuil_alerte']);

        $validated['user_id'] = auth()->id();

        $product = DB::transaction(function() use ($validated, $seuilAlerte){

            $product = Product::create($validated);

            // Crée une ligne de stock (quantité 0) pour chaque emplacement existant
            foreach (Emplacement::all() as $emplacement) {
                Stock::create([
                    'product_id'     => $product->id,
                    'emplacement_id' => $emplacement->id,
                    'quantite'       => 0,
                    'seuil_alerte'   => $seuilAlerte,
                ]);
            }
 
            return $product;

        });

        $product->load(['rayon.category', 'user', 'fournisseur', 'stocks.emplacement']);

        return response()->json($product, 200);
    }

    public function show(Product $product)
    {
        $product->load([
            'rayon.category',
            'user',
            'fournisseur',
            'stocks.emplacement',
            'mouvements.user',
            'mouvements.emplacement',
            'mouvements.emplacementDestination',
        ]);

        return response()->json($product);
    }

    public function update(Request $request, Product $product)
    {

         $validated = $request->validate([
            'code_barre'      => 'required|string|unique:products,code_barre,' . $product->id,
            'nom'             => 'required|string|max:255',
            'prix_unitaire'   => 'required|numeric|min:0',
            'prix_achat'      => 'required|numeric|min:0',
            'seuil_alerte'    => 'nullable|integer|min:0',
            'fournisseur_id'  => 'required|exists:fournisseurs,id',
            'date_expiration' => 'nullable|date',
            'image'           => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'rayon_id'        => 'required|exists:rayons,id',
        ]);
 
        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $seuilAlerte = $validated['seuil_alerte'] ?? null;
        unset($validated['seuil_alerte']);
 
        $product->update($validated);
 
        // Si un nouveau seuil d'alerte est fourni, on le répercute sur tous les emplacements du produit
        if (! is_null($seuilAlerte)) {
            $product->stocks()->update(['seuil_alerte' => $seuilAlerte]);
        }
 
        $product->load(['rayon.category', 'user', 'fournisseur', 'stocks.emplacement']);
 
        return response()->json($product);
    }

    public function destroy(Product $product)
    {

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();
        return response()->json(['message' => 'Produit supprimé avec succès.']);
    }
}
