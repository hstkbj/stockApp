<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\Http;

class MecefService{

    protected string $apiUrl;
    protected string $ifu;
    protected string $token;

    public function __construct()
    {
        $this->apiUrl = config('services.mecefservice.api_url');
        $this->ifu = config('services.mecefservice.ifu');
        $this->token = config('services.mecefservice.token');
    }

    /**
     * Étape 1 : Envoyer la facture à l'API
     * Retourne le UID + les totaux calculés
     */
    public function sendInvoice(Invoice $invoice, string $invoiceType = 'FV', string $paymentType = 'ESPECES'): array
    {
        $payload = [
            'ifu'      => $this->ifu,
            'type'     => $invoiceType,
            'operator' => ['name' => $invoice->user->full_name],
            'client'   => $this->buildClient($invoice),
            'items'    => $this->buildItems($invoice),
            'payment'  => [
                ['name' => $paymentType, 'amount' => (int) $invoice->total_ttc]
            ],
        ];



        return Http::withOptions(['verify' => false])->withToken($this->token)
            ->post($this->apiUrl, $payload)
            ->json();
    }

    public function sendAvoirCancelledInvoice(Invoice $invoice, string $invoiceType = 'FA', string $paymentType = 'ESPECES'){
        $invoice->load(['client','user','items.product','mecef']);

        /**
         * Code MECeF/DGI de la facture
         * que l'on souhaite annuler.
         */
        // Retirer les tirets pour obtenir exactement 24 caractères
        $reference = str_replace('-', '', $invoice->mecef->code_mecef_dgi);

        $payload = [
            'ifu'      => $this->ifu,
            'type'     => $invoiceType,
            'reference' => $reference,
            'operator' => ['name' => $invoice->user->full_name],
            'client'   => $this->buildClient($invoice),
            'items'    => $this->buildItems($invoice),
            'payment'  => [
                ['name' => $paymentType, 'amount' => (int) $invoice->total_ttc]
            ],
        ];

        return Http::withOptions([
            'verify' => false
        ])
        ->withToken($this->token)
        ->post($this->apiUrl, $payload)
        ->json();
    }

    public function sendPartialAvoirInvoice(Invoice $invoice, string $invoiceType = 'FA', string $paymentType = 'ESPECES'){

    }

    /**
     * Étape 2 : Confirmer la facture (finalisation)
     * Retourne codeMECeFDGI + qrCode
     */
    public function confirmInvoice(string $uid): array
    {
        $response = Http::withOptions(['verify' => false])->withToken($this->token)
            ->put($this->apiUrl . '/' . $uid . '/confirm');

        return $response->json();
    }

    /**
     * Annuler une facture en attente
     */
    public function cancelInvoice(string $uid): array
    {
        $response = Http::withOptions(['verify' => false])->withToken($this->token)
            ->put($this->apiUrl . '/' . $uid . '/annuler');

        return $response->json() ?? [];
    }

    private function buildClient(Invoice $invoice): ?array
    {
        if (!$invoice->client) return null;

        return [
            'name'    => $invoice->client->fullname,
            'ifu'     => $invoice->client->ifu,
            'contact' => $invoice->client->phone,
            'address' => $invoice->client->adresse,
        ];
    }

    private function buildItems(Invoice $invoice): array
    {
        $applyVat = !empty($invoice->total_tva) && (float)$invoice->total_tva > 0;

        return $invoice->items->map(function (InvoiceItem $item) use ($applyVat) {

            $unitPrice = (float) $item->unit_price;
            $qty = (int) $item->quantity;

            $ht = $unitPrice * $qty;

            $vatRate = $applyVat ? 18 : 0;

            $tva = $vatRate > 0 ? ($ht * $vatRate / 100) : 0;
            $ttc = $ht + $tva;

            return [
                'name'     => $item->product->nom,
                'price'    => $applyVat
                                ? (int) round($unitPrice * 1.18)
                                : (int) $unitPrice,
                'quantity' => $qty,
                'taxGroup' => $vatRate > 0 ? 'B' : 'A',
            ];
        })->toArray();
    }

}
