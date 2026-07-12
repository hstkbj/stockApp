<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Mouvement;
use App\Models\Stock;

class DashboardController extends Controller
{
     public function index()
    {
        $now = now();

        // ── 1. Cartes statistiques ────────────────────────────────
        $caMois = Invoice::whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->where('status', '!=', 'cancelled')
            ->sum('total_ttc');

        $nbVentesMois = Invoice::whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->where('status', '!=', 'cancelled')
            ->count();

        $nbClients = Client::count();

        $nbAlertes = Stock::whereColumn('quantite', '<=', 'seuil_alerte')->count();

        // ── 2. Montant total par mois (12 derniers mois) ──────────
        $ventesParMois = Invoice::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as mois, SUM(total_ttc) as total")
            ->where('status', '!=', 'cancelled')
            ->where('created_at', '>=', $now->copy()->subMonths(11)->startOfMonth())
            ->groupBy('mois')
            ->get();

        $chartMontants = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $key  = $date->format('Y-m');
            $montant = $ventesParMois->firstWhere('mois', $key)->total ?? 0;

            $chartMontants[] = [
                'mois'    => $date->translatedFormat('M Y'),
                'montant' => (float) $montant,
            ];
        }

        // ── 3. Top 5 produits les plus vendus ──────────────────────
        $topProduits = InvoiceItem::selectRaw('product_id, SUM(quantity) as total_vendu')
            ->whereHas('invoice', fn($q) => $q->where('status', '!=', 'cancelled'))
            ->groupBy('product_id')
            ->orderByDesc('total_vendu')
            ->take(5)
            ->with('product')
            ->get()
            ->map(fn($item) => [
                'nom'         => $item->product->nom ?? 'Produit supprimé',
                'total_vendu' => (int) $item->total_vendu,
            ]);

        // ── 4. Mouvements récents ──────────────────────────────────
        $mouvementsRecents = Mouvement::with('product', 'emplacement', 'user')
            ->latest()
            ->take(10)
            ->get();

        // ── 5. Ventes récentes ──────────────────────────────────────
        $ventesRecentes = Invoice::with('client')
            ->latest()
            ->take(10)
            ->get();

        return response()->json([
            'stats' => [
                'ca_mois'         => $caMois,
                'nb_ventes_mois'  => $nbVentesMois,
                'nb_clients'      => $nbClients,
                'nb_alertes'      => $nbAlertes,
            ],
            'chart_montants'      => $chartMontants,
            'top_produits'        => $topProduits,
            'mouvements_recents'  => $mouvementsRecents,
            'ventes_recentes'     => $ventesRecentes,
        ]);
    }
}
