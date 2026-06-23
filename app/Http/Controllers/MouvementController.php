<?php

namespace App\Http\Controllers;

use App\Models\Emplacement;
use Illuminate\Http\Request;
use App\Models\Mouvement;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;

class MouvementController extends Controller
{
    public function index()
    {
        $mouvements = Mouvement::with([
            'product',
            'user',
            'emplacement',
            'emplacementDestination',
        ])->latest()->get();

        return response()->json($mouvements);
    }

    /**
     * Liste les mouvements filtrés par emplacement.
     * GET /api/mouvements?emplacement=boutique
     * GET /api/mouvements?emplacement=magasin
    */
    public function indexByEmplacement(Request $request)
    {
        $request->validate([
            'emplacement' => 'required|in:boutique,magasin',
        ]);

        $emplacement = Emplacement::where('nom', $request->emplacement)->firstOrFail();

        $mouvements = Mouvement::with(['product', 'user', 'emplacement', 'emplacementDestination'])
            ->where('emplacement_id', $emplacement->id)
            ->latest()
            ->get();

        return response()->json($mouvements);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id'                 => 'required|exists:products,id',
            'emplacement_id'             => 'required|exists:emplacements,id',
            'emplacement_destination_id' => 'nullable|exists:emplacements,id|different:emplacement_id',
            'quantite'                   => 'required|integer|min:1',
            'type'                       => 'required|in:entree,sortie,transfert',
            'date'                       => 'required|date',
            'motif'                      => 'nullable|string|max:255',
        ]);

        // Un transfert doit obligatoirement avoir une destination
        if ($validated['type'] === 'transfert' && empty($validated['emplacement_destination_id'])) {
            return response()->json([
                'message' => 'Un transfert nécessite un emplacement de destination.',
            ], 422);
        }

        $mouvement = DB::transaction(function () use ($validated) {
            $stockSource = Stock::where('product_id', $validated['product_id'])
                ->where('emplacement_id', $validated['emplacement_id'])
                ->firstOrFail();

            // Vérification stock suffisant pour une sortie ou un transfert
            if (in_array($validated['type'], ['sortie', 'transfert'])) {
                if ($stockSource->quantite < $validated['quantite']) {
                    abort(422, 'Stock insuffisant. Disponible : ' . $stockSource->quantite);
                }
            }

            // Mise à jour du stock source
            if ($validated['type'] === 'entree') {
                $stockSource->increment('quantite', $validated['quantite']);
            }

            if ($validated['type'] === 'sortie') {
                $stockSource->decrement('quantite', $validated['quantite']);
            }

            if ($validated['type'] === 'transfert') {
                // Décrémenter la source
                $stockSource->decrement('quantite', $validated['quantite']);

                // Incrémenter la destination (la créer si elle n'existe pas encore)
                $stockDestination = Stock::firstOrCreate(
                    [
                        'product_id'     => $validated['product_id'],
                        'emplacement_id' => $validated['emplacement_destination_id'],
                    ],
                    [
                        'quantite'     => 0,
                        'seuil_alerte' => $stockSource->seuil_alerte,
                    ]
                );
                $stockDestination->increment('quantite', $validated['quantite']);
            }

            $validated['user_id'] = auth()->id();

            return Mouvement::create($validated);
        });

        $mouvement->load(['product', 'user', 'emplacement', 'emplacementDestination']);

        return response()->json($mouvement, 200);
    }

    public function show(Mouvement $mouvement)
    {
        $mouvement->load(['product', 'user', 'emplacement', 'emplacementDestination']);

        return response()->json($mouvement);
    }

    public function destroy(Mouvement $mouvement)
    {
        DB::transaction(function () use ($mouvement) {
            $stockSource = Stock::where('product_id', $mouvement->product_id)
                ->where('emplacement_id', $mouvement->emplacement_id)
                ->firstOrFail();

            if ($mouvement->type === 'entree') {
                // Annuler une entrée = décrémenter
                if ($stockSource->quantite < $mouvement->quantite) {
                    abort(422, 'Impossible d\'annuler : le stock actuel est insuffisant.');
                }
                $stockSource->decrement('quantite', $mouvement->quantite);
            }

            if ($mouvement->type === 'sortie') {
                // Annuler une sortie = réincrémenter
                $stockSource->increment('quantite', $mouvement->quantite);
            }

            if ($mouvement->type === 'transfert') {
                // Annuler un transfert = réincrémenter la source + décrémenter la destination
                $stockSource->increment('quantite', $mouvement->quantite);

                $stockDestination = Stock::where('product_id', $mouvement->product_id)
                    ->where('emplacement_id', $mouvement->emplacement_destination_id)
                    ->firstOrFail();

                if ($stockDestination->quantite < $mouvement->quantite) {
                    abort(422, 'Impossible d\'annuler le transfert : stock destination insuffisant.');
                }
                $stockDestination->decrement('quantite', $mouvement->quantite);
            }

            $mouvement->delete();
        });

        return response()->json(['message' => 'Mouvement supprimé et stock ajusté.']);
    }
}
