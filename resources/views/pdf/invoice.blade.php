<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #222;
        }

        table {
            border-collapse: collapse;
        }

        /* ===== En-tête ===== */
        .header-table {
            width: 100%;
            border-bottom: 2px solid #222;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .header-table td {
            vertical-align: middle;
        }

        .header-table h2 {
            margin: 0;
            font-size: 24px;
        }

        .header-table .logo-cell {
            text-align: right;
        }

        .header-table img {
            max-height: 50px;
        }

        /* ===== Bloc entreprise / n° facture ===== */
        .company-booking-table {
            width: 100%;
            margin-bottom: 15px;
        }

        .company-booking-table td {
            vertical-align: top;
        }

        .company-add {
            font-weight: bold;
        }

        .company-add span {
            display: block;
            font-weight: normal;
            font-size: 11px;
            color: #555;
        }

        .invoice-date {
            text-align: right;
        }

        .invoice-date h6 {
            margin: 2px 0;
            font-size: 12px;
        }

        .invoice-date span {
            font-weight: normal;
        }

        /* ===== Titres de section ===== */
        h5.section-title {
            background: #f5f5f5;
            padding: 5px 8px;
            margin: 15px 0 10px;
            font-size: 13px;
        }

        /* ===== Bloc client / émetteur / échéance ===== */
        .address-table {
            width: 100%;
            margin-bottom: 15px;
        }

        .address-table td {
            vertical-align: top;
            width: 33.33%;
            padding-right: 10px;
        }

        .address-table td.last-col {
            text-align: right;
            padding-right: 0;
        }

        .address-table .label {
            font-weight: bold;
            display: block;
            margin-bottom: 4px;
        }

        .inv-to-address {
            font-size: 11.5px;
            line-height: 1.5;
        }

        /* ===== Tableau des articles ===== */
        table.items-table {
            width: 100%;
            margin-bottom: 15px;
        }

        table.items-table th {
            background: #2b2b2b;
            color: #fff;
            padding: 6px 8px;
            text-align: left;
            font-size: 11px;
        }

        table.items-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #eee;
            font-size: 11.5px;
        }

        .text-start { text-align: left; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }

        /* ===== Totaux ===== */
        .totals-wrapper {
            width: 100%;
            margin-bottom: 5px;
        }

        .totals-wrapper td {
            vertical-align: top;
        }

        .totals-wrapper .totals-spacer {
            width: 60%;
        }

        .totals-table {
            width: 100%;
        }

        .totals-table td {
            padding: 3px 0;
            font-size: 12px;
        }

        .totals-table td:last-child {
            text-align: right;
        }

        .totalamt-table {
            width: 100%;
            background: #f5f5f5;
            margin-top: 5px;
        }

        .totalamt-table td {
            padding: 8px 6px;
            font-weight: bold;
            font-size: 14px;
        }

        .totalamt-table td:last-child {
            text-align: right;
        }

        /* ===== Conditions ===== */
        .terms-condition span {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        .terms-condition ol {
            font-size: 11px;
            margin: 0;
            padding-left: 18px;
            color: #555;
        }

        .thanks-msg {
            text-align: center;
            margin-top: 20px;
            font-style: italic;
            color: #555;
        }

        /* ===== Footer MECeF / DGI ===== */
        .mecef-footer {
            margin-top: 25px;
            width: 100%;
            border: 2px solid #000;
            border-radius: 10px;
            color: #000;
        }

        .mecef-footer td {
            padding: 20px;
            vertical-align: middle;
        }

        .mecef-footer .qr-cell {
            width: 40%;
            text-align: center;
        }

        .mecef-footer .qr-cell img {
            width: 120px;
            height: 120px;
        }

        .mecef-footer .data-cell {
            width: 60%;
        }

        .mecef-footer .mecef-title-block {
            text-align: center;
            margin-bottom: 10px;
        }

        .mecef-footer .mecef-title-block .mecef-title {
            display: block;
            font-size: 11px;
        }

        .mecef-footer .mecef-title-block .mecef-code {
            display: block;
            font-weight: bold;
            font-size: 12px;
        }

        .mecef-footer .other-table {
            width: 100%;
        }

        .mecef-footer .other-table td {
            padding: 1px 0;
            font-size: 11px;
        }

        .mecef-footer .other-table .titles-col {
            text-align: left;
        }

        .mecef-footer .other-table .data-col {
            text-align: right;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td><h2>Facture</h2></td>
            <td class="logo-cell">
                <img src="{{ public_path('assets/img/logo2.png') }}" alt="Logo">
            </td>
        </tr>
    </table>

    <table class="company-booking-table">
        <tr>
            <td>
                <div class="company-add">
                    {{ config('app.company_name', "Nom de l'entreprise") }}
                    <span>{{ config('app.company_address', '') }}</span>
                </div>
            </td>
            <td class="invoice-date">
                <h6>Facture N° : <span>{{ $invoice->invoice_number }}</span></h6>
                <h6>Date de facturation : <span>{{ $invoice->created_at->format('d-m-Y') }}</span></h6>
            </td>
        </tr>
    </table>

    <h5 class="section-title">Informations client</h5>
    <table class="address-table" style="margin-bottom: 40px;">
        <tr>
            <td>
                <span class="label">Facturé à :</span>
                <div class="inv-to-address">
                    @if($invoice->client)
                        {{ $invoice->client->fullname }} <br>
                        @if($invoice->client->address)
                            {{ $invoice->client->address }}<br>
                        @endif
                        @if($invoice->client->phone)
                            Tél : {{ $invoice->client->phone }}<br>
                        @endif
                        @if($invoice->client->email)
                            {{ $invoice->client->email }}
                        @endif
                    @else
                        {{ $invoice->anonymous_customer_name ?? 'Client anonyme' }}
                    @endif
                </div>
            </td>

            <td>
                <span class="label">Émis par :</span>
                <div class="inv-to-address">
                    {{ trim(($invoice->user->full_name ?? '')) ?: '—' }}
                </div>
            </td>

            <td class="last-col" >
                {{-- <span class="label">Échéance</span> --}}
                <div class="inv-to-address">
                    {{-- <h6 style="margin: 0 0 6px;">{{ \Carbon\Carbon::parse($invoice->echeance_at)->format('d/m/Y') }}</h6> --}}
                    <span class="label" style="margin-bottom: 2px;">Statut du paiement</span>
                    @php
                        $statusLabels = [
                            'draft'     => 'Brouillon',
                            'sent'      => 'Envoyée',
                            'paid'      => 'Payé',
                            'overdue'   => 'En retard',
                            'cancelled' => 'Annulée',
                        ];
                        $statusColors = [
                            'draft'     => ['bg' => '#e5e7eb', 'text' => '#374151'], // gris
                            'sent'      => ['bg' => '#dbeafe', 'text' => '#1e40af'], // bleu
                            'paid'      => ['bg' => '#00ff00', 'text' => '#063b06'], // vert
                            'overdue'   => ['bg' => '#fef3c7', 'text' => '#92400e'], // orange
                            'cancelled' => ['bg' => '#fee2e2', 'text' => '#991b1b'], // rouge
                        ];
                        $statusLabel = $statusLabels[$invoice->status] ?? strtoupper($invoice->status);
                        $statusColor = $statusColors[$invoice->status] ?? ['bg' => '#e5e7eb', 'text' => '#374151'];
                    @endphp
                    <strong >
                        {{ $statusLabel }}
                    </strong>
                </div>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Article</th>
                <th class="text-start">Quantité</th>
                <th class="text-start">Prix unitaire</th>
                <th class="text-start">TVA</th>
                <th class="text-end">Montant</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $index => $item)
                @php
                    $lineHt = $item->quantity * $item->unit_price;
                    $lineTva = round($lineHt * ($item->vat_rate / 100), 2);
                    $lineTotal = $lineHt + $lineTva;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->product->nom ?? 'Produit supprimé' }}</td>
                    <td class="text-start">{{ $item->quantity }}</td>
                    <td class="text-start">{{ number_format($item->unit_price, 2) }} FCFA</td>
                    <td class="text-start">{{ number_format($item->vat_rate, 2) }}%</td>
                    <td class="text-end">{{ number_format($lineTotal, 2) }} FCFA</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals-wrapper">
        <tr>
            <td class="totals-spacer"></td>
            <td>
                <table class="totals-table">
                    <tr>
                        <td><span>Montant HT</span></td>
                        <td>{{ number_format($invoice->total_ht, 2) }} FCFA</td>
                    </tr>
                    <tr>
                        <td><span>TVA</span></td>
                        <td>{{ number_format($invoice->total_tva, 2) }} FCFA</td>
                    </tr>
                </table>
                <table class="totalamt-table">
                    <tr>
                        <td>Montant Total</td>
                        <td>{{ number_format($invoice->total_ttc, 2) }} FCFA</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="terms-condition">
        <span>Conditions :</span>
        <ol>
            <li>Cette facture est émise conformément à la réglementation fiscale en vigueur.</li>
            <li>Merci de bien vouloir respecter les délais de paiement convenus.</li>
        </ol>
    </div>

    <div class="thanks-msg">
        Merci pour votre confiance
    </div>

    {{-- Footer MECeF / DGI : affiché uniquement si la facture a été normalisée --}}
    @if($mecef && $mecef->status === 'confirmed')
        <table class="mecef-footer">
            <tr>
                <td class="qr-cell">
                    @if($qrCodeBase64)
                        <img src="data:image/svg+xml;base64,{{ $qrCodeBase64 }}" alt="QR Code MECeF">
                    @endif
                </td>

                <td class="data-cell">
                    <div class="mecef-title-block">
                        <span class="mecef-title">Code MECeF/DGI</span>
                        <span class="mecef-code">{{ $mecef->code_mecef_dgi }}</span>
                    </div>

                    <table class="other-table">
                        <tr>
                            <td class="titles-col">MECeF NIM:</td>
                            <td class="data-col">{{ $mecef->nim }}</td>
                        </tr>
                        <tr>
                            <td class="titles-col">MECeF Compteurs:</td>
                            <td class="data-col">{{ $mecef->counters }}</td>
                        </tr>
                        <tr>
                            <td class="titles-col">MECeF Heure:</td>
                            <td class="data-col">{{ optional($mecef->mecef_datetime)->format('d/m/Y H:i:s') }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    @endif

</body>
</html>