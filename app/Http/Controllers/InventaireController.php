<?php

namespace App\Http\Controllers;

use App\Models\Emplacement;
use App\Models\Mouvement;
use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InventaireController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'emplacement' => 'required|in:boutique,magasin',
            'date'        => 'nullable|date',
        ]);

        $emplacement = Emplacement::where('nom', $validated['emplacement'])->firstOrFail();
        $date        = $validated['date'] ?? now()->toDateString();
        $cutoff      = Carbon::parse($date)->endOfDay();

        // 1. Stock ACTUEL réel par produit (vérité en temps réel, jamais recalculé)
        $stocksActuels = Stock::where('emplacement_id', $emplacement->id)
            ->with('product')
            ->get()
            ->keyBy('product_id');

        // 2. Mouvements du jour demandé (pour QENT / VTES à afficher)
        $mouvementsJour = Mouvement::where('emplacement_id', $emplacement->id)
            ->whereDate('date', $date)
            ->selectRaw('product_id, type, SUM(quantite) as total')
            ->groupBy('product_id', 'type')
            ->get()
            ->groupBy('product_id');

        // 3. Mouvements survenus APRÈS la date demandée (pour reconstituer un stock_fin passé)
        $mouvementsApres = Mouvement::where('emplacement_id', $emplacement->id)
            ->where('date', '>', $cutoff)
            ->selectRaw('product_id, type, SUM(quantite) as total')
            ->groupBy('product_id', 'type')
            ->get()
            ->groupBy('product_id');

        $result = [];

        foreach ($stocksActuels as $productId => $stock) {

            $stockActuel = $stock->quantite;

            // Reconstituer le stock fin à la date demandée en "annulant" ce qui s'est passé après
            $apres         = $mouvementsApres->get($productId, collect());
            $entreesApres  = $apres->firstWhere('type', 'entree')?->total ?? 0;
            $sortiesApres  = $apres->firstWhere('type', 'sortie')?->total ?? 0;
            $stockFin      = $stockActuel - $entreesApres + $sortiesApres;

            // Mouvements du jour lui-même
            $jour = $mouvementsJour->get($productId, collect());
            $qent = $jour->firstWhere('type', 'entree')?->total ?? 0;
            $vtes = $jour->firstWhere('type', 'sortie')?->total ?? 0;

            // SDEB déduit, jamais additionné à l'aveugle
            $stockDebut = $stockFin - $qent + $vtes;

            $result[] = [
                'product_id'      => $productId,
                'code_barre'      => $stock->product->code_barre,
                'nom'             => $stock->product->nom,
                'stock_debut'     => $stockDebut,
                'quantite_entree' => $qent,
                'quantite_sortie' => $vtes,
                'stock_fin'       => $stockFin,
                'seuil_alerte'    => $stock->seuil_alerte,
            ];
        }

        return response()->json([
            'date'         => $date,
            'emplacement'  => $emplacement->nom,
            'inventaire'   => $result,
        ]);
    }
}
