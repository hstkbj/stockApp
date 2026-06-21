<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Fournisseur;

class FournisseurController extends Controller
{

    public function index()
    {
        $fournisseurs = Fournisseur::withCount('aprovisionnements')->get();
        return response()->json($fournisseurs);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom'       => 'required|string|max:255',
            'email'     => 'nullable|email|unique:fournisseurs,email',
            'telephone' => 'nullable|string|max:20',
            'adresse'   => 'nullable|string|max:500',
        ]);

        $fournisseur = Fournisseur::create($validated);
        return response()->json($fournisseur, 200);
    }

    public function show(Fournisseur $fournisseur)
    {
        $fournisseur->load('aprovisionnements.items.product');
        return response()->json($fournisseur);
    }

    public function update(Request $request, Fournisseur $fournisseur)
    {
        $validated = $request->validate([
            'nom'       => 'required|string|max:255',
            'email'     => 'nullable|email|unique:fournisseurs,email,' . $fournisseur->id,
            'telephone' => 'nullable|string|max:20',
            'adresse'   => 'nullable|string|max:500',
        ]);

        $fournisseur->update($validated);
        return response()->json($fournisseur);
    }

    public function destroy(Fournisseur $fournisseur)
    {
        if ($fournisseur->aprovisionnements()->count() > 0) {
            return response()->json([
                'message' => 'Impossible de supprimer un fournisseur ayant des approvisionnements.'
            ], 422);
        }

        $fournisseur->delete();
        return response()->json(['message' => 'Fournisseur supprimé avec succès.']);
    }

}
