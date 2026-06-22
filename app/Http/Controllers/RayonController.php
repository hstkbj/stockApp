<?php

namespace App\Http\Controllers;

use App\Models\Rayon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class RayonController extends Controller
{
    public function index(){
        $rayons = Rayon::with('category')->latest()->get();
 
        return response()->json($rayons);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
        ]);
 
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
 
        $rayon = Rayon::create($validator->validated());
 
        return response()->json([
            'message' => 'Rayon créé avec succès.',
            'rayon' => $rayon->load('category'),
        ], 200);
    }

    public function show(Rayon $rayon): JsonResponse
    {
        return response()->json($rayon->load('category', 'products'));
    }

    public function update(Request $request, Rayon $rayon): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
        ]);
 
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
 
        $rayon->update($validator->validated());
 
        return response()->json([
            'message' => 'Rayon mis à jour avec succès.',
            'rayon' => $rayon->load('category'),
        ]);
    }

    public function destroy(Rayon $rayon): JsonResponse
    {
        $rayon->delete();
 
        return response()->json([
            'message' => 'Rayon supprimé avec succès.',
        ]);
    }

}
