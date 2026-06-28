<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .wrapper {
            max-width: 600px;
            margin: 30px auto;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .header {
            background: #002D5D;
            color: #fff;
            padding: 25px 30px;
            text-align: center;
        }
        .header h1 { font-size: 22px; margin: 0; }
        .header p  { margin: 5px 0 0; font-size: 13px; opacity: 0.8; }
        .body { padding: 30px; }
        .body p { font-size: 14px; color: #444; line-height: 1.6; margin-bottom: 12px; }
        .invoice-box {
            background: #f8f9fa;
            border-left: 4px solid #002D5D;
            border-radius: 4px;
            padding: 15px 20px;
            margin: 20px 0;
        }
        .invoice-box table { width: 100%; border-collapse: collapse; }
        .invoice-box td { padding: 6px 0; font-size: 13px; color: #333; }
        .invoice-box td:last-child { text-align: right; font-weight: bold; }
        .invoice-box .total-row td {
            border-top: 1px solid #ddd;
            padding-top: 10px;
            font-size: 15px;
            color: #002D5D;
        }
        .attachment-note {
            background: #e8f4fd;
            border-radius: 6px;
            padding: 12px 16px;
            font-size: 13px;
            color: #0c5460;
            margin: 20px 0;
        }
        .attachment-note i { margin-right: 6px; }
        .footer {
            text-align: center;
            padding: 20px 30px;
            background: #f8f9fa;
            font-size: 12px;
            color: #999;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="wrapper">

        <!-- En-tête -->
        <div class="header">
            <h1>{{ config('app.name') }}</h1>
            <p>Votre facture est disponible</p>
        </div>

        <!-- Corps -->
        <div class="body">
            <p>Bonjour <strong>{{ $invoice->client->fullname }}</strong>,</p>
            <p>
                Nous vous remercions pour votre confiance. Veuillez trouver ci-joint
                votre facture <strong>{{ $invoice->invoice_number }}</strong>.
            </p>

            <!-- Récapitulatif -->
            <div class="invoice-box">
                <table>
                    <tr>
                        <td>N° Facture</td>
                        <td>{{ $invoice->invoice_number }}</td>
                    </tr>
                    <tr>
                        <td>Date</td>
                        <td>{{ \Carbon\Carbon::parse($invoice->due_at)->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td>Date d'échéance</td>
                        <td>{{ \Carbon\Carbon::parse($invoice->echeance_at)->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td>Total HT</td>
                        <td>{{ number_format($invoice->total_ht, 0, ',', ' ') }} FCFA</td>
                    </tr>
                    <tr>
                        <td>TVA</td>
                        <td>{{ number_format($invoice->total_tva, 0, ',', ' ') }} FCFA</td>
                    </tr>
                    <tr class="total-row">
                        <td>Total TTC</td>
                        <td>{{ number_format($invoice->total_ttc, 0, ',', ' ') }} FCFA</td>
                    </tr>
                </table>
            </div>

            <!-- Note pièce jointe -->
            <div class="attachment-note">
                📎 La facture PDF est disponible en pièce jointe de cet email.
            </div>

            <p>
                Pour toute question concernant cette facture,
                n'hésitez pas à nous contacter.
            </p>

            <p>Cordialement,<br><strong>{{ config('app.name') }}</strong></p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre directement.</p>
            <p>© {{ date('Y') }} {{ config('app.name') }} — Tous droits réservés</p>
        </div>

    </div>
</body>
</html>