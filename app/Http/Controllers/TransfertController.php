<?php

namespace App\Http\Controllers;

use App\Models\Emplacement;
use App\Models\Mouvement;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransfertController extends Controller
{
    /**
     * Transfert Boutique -> Magasin
     */
    public function versMagasin(Request $request)
    {
        return $this->transferer($request, Emplacement::BOUTIQUE, Emplacement::MAGASIN);
    }

    /**
     * Transfert Magasin -> Boutique
     */
    public function versBoutique(Request $request)
    {
        return $this->transferer($request, Emplacement::MAGASIN, Emplacement::BOUTIQUE);
    }

    /**
     * Logique commune de transfert entre deux emplacements identifiés par leur nom.
     */
    private function transferer(Request $request, string $nomSource, string $nomDestination)
    {
        $validated = $request->validate([
            'product_id'  => 'required|exists:products,id',
            'quantite'    => 'required|integer|min:1',
            'date'        => 'required|date',
            'motif'       => 'nullable|string|max:255',
        ]);

        $emplacementSource = Emplacement::where('nom', $nomSource)->firstOrFail();
        $emplacementDestination = Emplacement::where('nom', $nomDestination)->firstOrFail();

        [$mouvementSortie, $mouvementEntree] = DB::transaction(function () use ($validated, $emplacementSource, $emplacementDestination) {

            // Stock source : créé à 0 s'il n'existe pas encore
            $stockSource = Stock::firstOrCreate(
                [
                    'product_id'     => $validated['product_id'],
                    'emplacement_id' => $emplacementSource->id,
                ],
                [
                    'quantite'     => 0,
                    'seuil_alerte' => 10,
                ]
            );

            if ($stockSource->quantite < $validated['quantite']) {
                abort(422, 'Stock insuffisant à la source. Disponible : ' . $stockSource->quantite);
            }

            // Stock destination : créé à 0 s'il n'existe pas encore
            $stockDestination = Stock::firstOrCreate(
                [
                    'product_id'     => $validated['product_id'],
                    'emplacement_id' => $emplacementDestination->id,
                ],
                [
                    'quantite'     => 0,
                    'seuil_alerte' => $stockSource->seuil_alerte,
                ]
            );

            $stockSource->decrement('quantite', $validated['quantite']);
            $stockDestination->increment('quantite', $validated['quantite']);

            // Mouvement de SORTIE côté source
            $mouvementSortie = Mouvement::create([
                'product_id'                 => $validated['product_id'],
                'emplacement_id'              => $emplacementSource->id,
                'emplacement_destination_id'  => $emplacementDestination->id,
                'quantite'                    => $validated['quantite'],
                'type'                        => 'sortie',
                'date'                        => $validated['date'],
                'motif'                       => $validated['motif'] ?? 'Transfert vers ' . $emplacementDestination->nom,
                'user_id'                     => auth()->id(),
            ]);

            // Mouvement d'ENTRÉE côté destination
            $mouvementEntree = Mouvement::create([
                'product_id'                 => $validated['product_id'],
                'emplacement_id'              => $emplacementDestination->id,
                'emplacement_destination_id'  => $emplacementSource->id,
                'quantite'                    => $validated['quantite'],
                'type'                        => 'entree',
                'date'                        => $validated['date'],
                'motif'                       => $validated['motif'] ?? 'Transfert depuis ' . $emplacementSource->nom,
                'user_id'                     => auth()->id(),
            ]);

            return [$mouvementSortie, $mouvementEntree];
        });

        $mouvementSortie->load(['product', 'user', 'emplacement', 'emplacementDestination']);
        $mouvementEntree->load(['product', 'user', 'emplacement', 'emplacementDestination']);

        return response()->json([
            'sortie' => $mouvementSortie,
            'entree' => $mouvementEntree,
        ], 200);
    }
}
