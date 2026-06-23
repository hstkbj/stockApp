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

    public function indexProductBoutique()
    {
        $emplacement = Emplacement::where('nom', Emplacement::BOUTIQUE)->firstOrFail();

        $products = Product::with(['rayon.category', 'fournisseur'])
            ->with(['stocks' => fn($q) => $q->where('emplacement_id', $emplacement->id)])
            ->latest()
            ->get();

        return response()->json($products);
    }

    public function indexProductMagasin()
    {
        $emplacement = Emplacement::where('nom', Emplacement::MAGASIN)->firstOrFail();

        $products = Product::with(['rayon.category', 'fournisseur'])
            ->with(['stocks' => fn($q) => $q->where('emplacement_id', $emplacement->id)])
            ->latest()
            ->get();

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
            'fournisseur_id'  => 'nullable|exists:fournisseurs,id',
            'date_expiration' => 'nullable|date',
            'image'           => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'rayon_id'        => 'required|exists:rayons,id',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $seuilAlerte = $validated['seuil_alerte'];
        unset($validated['seuil_alerte']);

        $validated['user_id'] = auth()->id();

        $product = DB::transaction(function () use ($validated, $seuilAlerte) {
            $product = Product::create($validated);

            // Enregistre automatiquement dans TOUS les emplacements (boutique + magasin)
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

    public function storeProductBoutique(Request $request)
    {
        $validated = $request->validate([
            'code_barre'      => 'required|string|unique:products,code_barre',
            'nom'             => 'required|string|max:255',
            'prix_unitaire'   => 'required|numeric|min:0',
            'prix_achat'      => 'required|numeric|min:0',
            'seuil_alerte'    => 'required|integer|min:0',
            'fournisseur_id'  => 'nullable|exists:fournisseurs,id',
            'date_expiration' => 'nullable|date',
            'quantite'        => 'nullable|numeric|min:0',
            'image'           => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'rayon_id'        => 'required|exists:rayons,id',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $seuilAlerte = $validated['seuil_alerte'];
        $quantite    = $validated['quantite'] ?? 0;
        unset($validated['seuil_alerte'], $validated['quantite']);

        $validated['user_id'] = auth()->id();

        $product = DB::transaction(function () use ($validated, $seuilAlerte, $quantite) {
            $product = Product::create($validated);

            $emplacement = Emplacement::where('nom', Emplacement::BOUTIQUE)->firstOrFail();

            Stock::create([
                'product_id'     => $product->id,
                'emplacement_id' => $emplacement->id,
                'quantite'       => $quantite,
                'seuil_alerte'   => $seuilAlerte,
            ]);

            return $product;
        });

        $product->load(['rayon.category', 'user', 'fournisseur', 'stocks.emplacement']);

        return response()->json($product, 200);
    }

    public function storeProductMagasin(Request $request)
    {
        $validated = $request->validate([
            'code_barre'      => 'required|string|unique:products,code_barre',
            'nom'             => 'required|string|max:255',
            'prix_unitaire'   => 'required|numeric|min:0',
            'prix_achat'      => 'required|numeric|min:0',
            'seuil_alerte'    => 'required|integer|min:0',
            'fournisseur_id'  => 'nullable|exists:fournisseurs,id',
            'date_expiration' => 'nullable|date',
            'quantite'        => 'nullable|numeric|min:0',
            'image'           => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'rayon_id'        => 'required|exists:rayons,id',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $seuilAlerte = $validated['seuil_alerte'];
        $quantite    = $validated['quantite'] ?? 0;
        unset($validated['seuil_alerte'],$validated['quantite']);
        $validated['user_id'] = auth()->id();

        $product = DB::transaction(function () use ($validated, $seuilAlerte, $quantite) {
            $product = Product::create($validated);

            $emplacement = Emplacement::where('nom', Emplacement::MAGASIN)->firstOrFail();

            Stock::create([
                'product_id'     => $product->id,
                'emplacement_id' => $emplacement->id,
                'quantite'       => $quantite,
                'seuil_alerte'   => $seuilAlerte,
            ]);

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
            'fournisseur_id'  => 'nullable|exists:fournisseurs,id',
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

        DB::transaction(function () use ($validated, $seuilAlerte, $product) {
            $product->update($validated);

            // Met à jour le seuil d'alerte sur TOUS les emplacements du produit
            if (! is_null($seuilAlerte)) {
                $product->stocks()->update(['seuil_alerte' => $seuilAlerte]);
            }
        });

        $product->load(['rayon.category', 'user', 'fournisseur', 'stocks.emplacement']);

        return response()->json($product);
    }

    public function updateProductBoutique(Request $request, Product $product)
    {
        $validated = $request->validate([
            'code_barre'      => 'required|string|unique:products,code_barre,' . $product->id,
            'nom'             => 'required|string|max:255',
            'prix_unitaire'   => 'required|numeric|min:0',
            'prix_achat'      => 'required|numeric|min:0',
            'seuil_alerte'    => 'nullable|integer|min:0',
            'quantite'        => 'nullable|integer|min:0',
            'fournisseur_id'  => 'nullable|exists:fournisseurs,id',
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
        $quantite    = $validated['quantite'] ?? null;
        unset($validated['seuil_alerte'], $validated['quantite']);

        DB::transaction(function () use ($validated, $seuilAlerte, $quantite, $product) {
            $product->update($validated);

            $emplacement = Emplacement::where('nom', Emplacement::BOUTIQUE)->firstOrFail();

            $stockUpdate = [];

            if (! is_null($seuilAlerte)) {
                $stockUpdate['seuil_alerte'] = $seuilAlerte;
            }
            if (! is_null($quantite)) {
                $stockUpdate['quantite'] = $quantite;
            }

            if (! empty($stockUpdate)) {
                $product->stocks()
                    ->where('emplacement_id', $emplacement->id)
                    ->update($stockUpdate);
            }
        });

        $product->load(['rayon.category', 'user', 'fournisseur', 'stocks.emplacement']);

        return response()->json($product);
    }

    public function updateProductMagasin(Request $request, Product $product)
    {
        $validated = $request->validate([
            'code_barre'      => 'required|string|unique:products,code_barre,' . $product->id,
            'nom'             => 'required|string|max:255',
            'prix_unitaire'   => 'required|numeric|min:0',
            'prix_achat'      => 'required|numeric|min:0',
            'seuil_alerte'    => 'nullable|integer|min:0',
            'quantite'        => 'nullable|integer|min:0',
            'fournisseur_id'  => 'nullable|exists:fournisseurs,id',
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
        $quantite    = $validated['quantite'] ?? null;
        unset($validated['seuil_alerte'], $validated['quantite']);

        DB::transaction(function () use ($validated, $seuilAlerte, $quantite, $product) {
            $product->update($validated);

            $emplacement = Emplacement::where('nom', Emplacement::MAGASIN)->firstOrFail();

            $stockUpdate = [];

            if (! is_null($seuilAlerte)) {
                $stockUpdate['seuil_alerte'] = $seuilAlerte;
            }
            if (! is_null($quantite)) {
                $stockUpdate['quantite'] = $quantite;
            }

            if (! empty($stockUpdate)) {
                $product->stocks()
                    ->where('emplacement_id', $emplacement->id)
                    ->update($stockUpdate);
            }
        });

        $product->load(['rayon.category', 'user', 'fournisseur', 'stocks.emplacement']);

        return response()->json($product);
    }

    public function destroy(Product $product)
    {
        DB::transaction(function () use ($product) {
            // Les stocks associés sont supprimés automatiquement (cascade)
            $product->delete();

            // On supprime l'image seulement si la suppression en base a réussi
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
        });

        return response()->json(['message' => 'Produit supprimé avec succès.']);
    }
}
