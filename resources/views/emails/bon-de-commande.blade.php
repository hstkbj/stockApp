<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bon de commande {{ $aprovisionnement->reference }}</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .wrapper { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .header { background: #002D5D; color: #fff; padding: 25px 30px; text-align: center; }
        .header h1 { font-size: 22px; margin: 0; }
        .header p  { margin: 5px 0 0; font-size: 13px; opacity: 0.8; }
        .body { padding: 30px; }
        .body p { font-size: 14px; color: #444; line-height: 1.6; margin-bottom: 12px; }
        .commande-box { background: #f8f9fa; border-left: 4px solid #002D5D; border-radius: 4px; padding: 15px 20px; margin: 20px 0; }
        .commande-box table { width: 100%; border-collapse: collapse; }
        .commande-box td { padding: 6px 0; font-size: 13px; color: #333; }
        .commande-box td:last-child { text-align: right; font-weight: bold; }
        .commande-box .total-row td { border-top: 1px solid #ddd; padding-top: 10px; font-size: 15px; color: #002D5D; }
        .attachment-note { background: #e8f4fd; border-radius: 6px; padding: 12px 16px; font-size: 13px; color: #0c5460; margin: 20px 0; }
        .footer { text-align: center; padding: 20px 30px; background: #f8f9fa; font-size: 12px; color: #999; border-top: 1px solid #eee; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>{{ config('app.name') }}</h1>
            <p>Bon de commande</p>
        </div>
        <div class="body">
            <p>Bonjour <strong>{{ $aprovisionnement->fournisseur->nom }}</strong>,</p>
            <p>
                Nous vous adressons ci-joint notre bon de commande
                <strong>{{ $aprovisionnement->reference }}</strong>.
                Merci de bien vouloir procéder à la livraison dans les meilleurs délais.
            </p>

            <div class="commande-box">
                <table>
                    <tr>
                        <td>Référence</td>
                        <td>{{ $aprovisionnement->reference }}</td>
                    </tr>
                    <tr>
                        <td>Date</td>
                        <td>{{ \Carbon\Carbon::parse($aprovisionnement->date_approvisionnement)->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td>Nombre d'articles</td>
                        <td>{{ $aprovisionnement->items->count() }}</td>
                    </tr>
                    <tr class="total-row">
                        <td>Montant total</td>
                        <td>{{ number_format($aprovisionnement->montant_total, 0, ',', ' ') }} FCFA</td>
                    </tr>
                </table>
            </div>

            <div class="attachment-note">
                📎 Le bon de commande détaillé est disponible en pièce jointe de cet email.
            </div>

            <p>Cordialement,<br><strong>{{ config('app.name') }}</strong></p>
        </div>
        <div class="footer">
            <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre directement.</p>
            <p>© {{ date('Y') }} {{ config('app.name') }} — Tous droits réservés</p>
        </div>
    </div>
</body>
</html>