<?php

namespace App\Http\Controllers;

use App\Models\Emplacement;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    const TVA_BENIN = 18; // 18%

    public function index(Request $request){

        $request->validate([
            'emplacement' => 'required|in:boutique,magasin',
        ]);

        $emplacement = Emplacement::where('nom', $request->emplacement)->firstOrFail();

        $invoices = Invoice::with(['client', 'user', 'emplacement', 'items.product'])
            ->where('emplacement_id', $emplacement->id)
            ->latest()
            ->get();

        return response()->json($invoices);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id'          => 'nullable|exists:clients,id',
            'emplacement_id'     => 'required|exists:emplacements,id',
            'due_at'             => 'required|date',
            'echeance_at'        => 'required|date',
            'is_taxable'         => 'required|boolean', // true = 18% / false = exonéré
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $invoice = DB::transaction(function () use ($validated) {
            // Calcul des totaux
            $totalHt  = collect($validated['items'])->sum(
                fn($item) => $item['quantity'] * $item['unit_price']
            );
            $tauxTva  = $validated['is_taxable'] ? self::TVA_BENIN / 100 : 0;
            $totalTva = round($totalHt * $tauxTva, 2);
            $totalTtc = round($totalHt + $totalTva, 2);

            // Client anonyme si aucun client sélectionné
            $anonymousName = null;
            if (empty($validated['client_id'])) {
                $anonymousName = 'Client-' . strtoupper(Str::random(6));
            }

            $invoice = Invoice::create([
                'invoice_number'          => 'INV-' . strtoupper(Str::random(8)) . '-' . now()->format('Ymd'),
                'status'                  => 'draft', // toujours draft à la création
                'due_at'                  => $validated['due_at'],
                'echeance_at'             => $validated['echeance_at'],
                'total_ht'                => $totalHt,
                'total_tva'               => $totalTva,
                'total_ttc'               => $totalTtc,
                'client_id'               => $validated['client_id'] ?? null,
                'anonymous_customer_name' => $anonymousName,
                'emplacement_id'          => $validated['emplacement_id'],
                'user_id'                 => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                InvoiceItem::create([
                    'invoice_id'  => $invoice->id,
                    'product_id'  => $item['product_id'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'vat_rate'    => $validated['is_taxable'] ? self::TVA_BENIN : 0,
                ]);
            }

            // Vérifier le stock puis déduire immédiatement
            $this->verifierStockSuffisant($invoice);
            $this->deduireStock($invoice);

            return $invoice;
        });

        $invoice->load(['client', 'user', 'emplacement', 'items.product']);

        return response()->json($invoice, 201);
    }

    public function show(Invoice $invoice)
    {
        $invoice->load([
            'client', 
            'user', 
            'mecef' => fn($query) => $query->latest(),
            'emplacement', 
            'items.product'
            ]);

        return response()->json($invoice);
    }

    public function update(Request $request, Invoice $invoice)
    {
        if ($invoice->status === 'cancelled') {
            return response()->json([
                'message' => 'Une facture annulée ne peut pas être modifiée.',
            ], 422);
        }

        $validated = $request->validate([
            'client_id'          => 'nullable|exists:clients,id',
            'emplacement_id'     => 'required|exists:emplacements,id',
            'due_at'             => 'required|date',
            'echeance_at'        => 'required|date',
            'status'            => 'required',
            'is_taxable'         => 'required|boolean',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($invoice, $validated) {
            // Remettre l'ancien stock avant modification
            $this->remettreStock($invoice);

            // Recalcul des totaux
            $totalHt  = collect($validated['items'])->sum(
                fn($item) => $item['quantity'] * $item['unit_price']
            );
            $tauxTva  = $validated['is_taxable'] ? self::TVA_BENIN / 100 : 0;
            $totalTva = round($totalHt * $tauxTva, 2);
            $totalTtc = round($totalHt + $totalTva, 2);

            $anonymousName = $invoice->anonymous_customer_name;
            if (! empty($validated['client_id'])) {
                $anonymousName = null;
            } elseif (empty($validated['client_id']) && empty($invoice->client_id)) {
                $anonymousName = $anonymousName ?? 'Client-' . strtoupper(Str::random(6));
            }

            $invoice->update([
                'client_id'               => $validated['client_id'] ?? null,
                'anonymous_customer_name' => $anonymousName,
                'emplacement_id'          => $validated['emplacement_id'],
                'due_at'                  => $validated['due_at'],
                'status'                  => $validated['status'],
                'echeance_at'             => $validated['echeance_at'],
                'total_ht'                => $totalHt,
                'total_tva'               => $totalTva,
                'total_ttc'               => $totalTtc,
            ]);

            // Remplacer les lignes
            $invoice->items()->delete();
            foreach ($validated['items'] as $item) {
                InvoiceItem::create([
                    'invoice_id'  => $invoice->id,
                    'product_id'  => $item['product_id'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'vat_rate'    => $validated['is_taxable'] ? self::TVA_BENIN : 0,
                ]);
            }

            // Vérifier et déduire le nouveau stock
            $this->verifierStockSuffisant($invoice->fresh('items.product'));
            $this->deduireStock($invoice->fresh('items'));
        });

        $invoice->load(['client', 'user', 'emplacement', 'items.product']);

        return response()->json($invoice);
    }

    public function updateStatus(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'status' => 'required|in:draft,sent,paid,overdue,cancelled',
        ]);

        $ancienStatus  = $invoice->status;
        $nouveauStatus = $validated['status'];

        if ($ancienStatus === $nouveauStatus) {
            return response()->json($invoice);
        }

        if ($ancienStatus === 'cancelled') {
            return response()->json([
                'message' => 'Une facture annulée ne peut plus changer de statut.',
            ], 422);
        }

        DB::transaction(function () use ($invoice, $ancienStatus, $nouveauStatus) {
            if ($nouveauStatus === 'cancelled' && $ancienStatus !== 'cancelled') {
                $this->remettreStock($invoice);
            }

            $invoice->update(['status' => $nouveauStatus]);
        });

        $invoice->load(['client', 'user', 'emplacement', 'items.product']);

        return response()->json($invoice);
    }

    public function cancel(Invoice $invoice)
    {
        if ($invoice->status === 'cancelled') {
            return response()->json(['message' => 'Facture déjà annulée.'], 422);
        }

        DB::transaction(function () use ($invoice) {
            $this->remettreStock($invoice);
            $invoice->update(['status' => 'cancelled']);
        });

        $invoice->load(['client', 'user', 'emplacement', 'items.product']);

        return response()->json($invoice);
    }

    public function destroy(Invoice $invoice)
    {
        DB::transaction(function () use ($invoice) {
            // Si déjà annulée, le stock a déjà été remis via cancel()
            if ($invoice->status !== 'cancelled') {
                $this->remettreStock($invoice);
            }

            $invoice->delete(); // items supprimés par cascade
        });

        return response()->json(['message' => 'Facture supprimée avec succès.']);
    }

    // -------------------------------------------------------------------------
    // Helpers privés
    // -------------------------------------------------------------------------

    private function verifierStockSuffisant(Invoice $invoice): void
    {
        $invoice->load('items.product');

        foreach ($invoice->items as $item) {
            $stock      = Stock::where('product_id', $item->product_id)
                ->where('emplacement_id', $invoice->emplacement_id)
                ->first();

            $disponible = $stock?->quantite ?? 0;

            if ($disponible < $item->quantity) {
                abort(422, "Stock insuffisant pour \"{$item->product->nom}\". Disponible : {$disponible}, demandé : {$item->quantity}.");
            }
        }
    }

    private function deduireStock(Invoice $invoice): void
    {
        $invoice->load('items');

        foreach ($invoice->items as $item) {
            Stock::where('product_id', $item->product_id)
                ->where('emplacement_id', $invoice->emplacement_id)
                ->decrement('quantite', $item->quantity);
        }
    }

    private function remettreStock(Invoice $invoice): void
    {
        $invoice->load('items');

        foreach ($invoice->items as $item) {
            Stock::where('product_id', $item->product_id)
                ->where('emplacement_id', $invoice->emplacement_id)
                ->increment('quantite', $item->quantity);
        }
    }
}
