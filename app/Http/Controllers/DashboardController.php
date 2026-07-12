<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Mouvement;
use App\Models\Stock;
use Carbon\Carbon;

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

        // ── 2. Montant total par mois (année civile en cours : Janvier → Décembre) ──
        $ventesParMois = Invoice::selectRaw('MONTH(created_at) as mois_num, SUM(total_ttc) as total')
            ->where('status', '!=', 'cancelled')
            ->whereYear('created_at', $now->year)
            ->groupBy('mois_num')
            ->get();

        $chartMontants = [];
        for ($m = 1; $m <= 12; $m++) {
            $montant = $ventesParMois->firstWhere('mois_num', $m)->total ?? 0;

            $chartMontants[] = [
                'mois'    => Carbon::create($now->year, $m, 1)->translatedFormat('M Y'),
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
