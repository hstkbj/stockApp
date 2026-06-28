<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bon de commande {{ $aprovisionnement->reference }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; padding: 30px; }

        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #002D5D; }
        .header h2 { font-size: 24px; color: #002D5D; font-weight: bold; }
        .header .ref { text-align: right; font-size: 11px; color: #666; }
        .header .ref strong { font-size: 14px; color: #002D5D; display: block; margin-bottom: 3px; }

        .info-section { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .info-block { width: 48%; }
        .info-block .label { font-size: 10px; color: #999; text-transform: uppercase; margin-bottom: 5px; }
        .info-block p { font-size: 11px; margin-bottom: 3px; }

        .badge { padding: 3px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; background: #fff3cd; color: #856404; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        thead tr { background: #002D5D; color: #fff; }
        thead th { padding: 8px 10px; text-align: left; font-size: 11px; }
        tbody tr:nth-child(even) { background: #f8f9fa; }
        tbody td { padding: 7px 10px; font-size: 11px; border-bottom: 1px solid #eee; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .totals { margin-left: auto; width: 260px; }
        .totals table { margin-bottom: 0; }
        .totals tbody tr { background: transparent !important; }
        .totals td { border: none; padding: 4px 8px; font-size: 11px; }
        .totals .total-row td { background: #002D5D !important; color: #fff; font-weight: bold; font-size: 13px; }
        .totals .total-row td:last-child { text-align: right; }

        .footer-note { margin-top: 25px; padding: 12px 15px; background: #f8f9fa; border-radius: 4px; font-size: 11px; color: #666; }
        .signature { margin-top: 30px; display: flex; justify-content: flex-end; }
        .signature-box { text-align: center; font-size: 11px; }
        .signature-box .line { border-top: 1px solid #333; width: 180px; margin-top: 40px; padding-top: 5px; }
    </style>
</head>
<body>

    <!-- En-tête -->
    <div class="header">
        <div>
            <h2>BON DE COMMANDE</h2>
            <p style="font-size:11px;color:#666;margin-top:4px;">{{ config('app.name') }}</p>
        </div>
        <div class="ref">
            <strong>{{ $aprovisionnement->reference }}</strong>
            Date : {{ \Carbon\Carbon::parse($aprovisionnement->date_approvisionnement)->format('d/m/Y') }}<br>
            Statut : <span class="badge">⏳ En attente</span>
        </div>
    </div>

    <!-- Infos fournisseur / commande -->
    <div class="info-section">
        <div class="info-block">
            <div class="label">Fournisseur</div>
            <p><strong>{{ $aprovisionnement->fournisseur->nom }}</strong></p>
            @if($aprovisionnement->fournisseur->email)
                <p>{{ $aprovisionnement->fournisseur->email }}</p>
            @endif
            @if($aprovisionnement->fournisseur->telephone)
                <p>{{ $aprovisionnement->fournisseur->telephone }}</p>
            @endif
            @if($aprovisionnement->fournisseur->adresse)
                <p>{{ $aprovisionnement->fournisseur->adresse }}</p>
            @endif
        </div>
        <div class="info-block" style="text-align:right;">
            <div class="label">Informations commande</div>
            <p>Référence : <strong>{{ $aprovisionnement->reference }}</strong></p>
            <p>Date : <strong>{{ \Carbon\Carbon::parse($aprovisionnement->date_approvisionnement)->format('d/m/Y') }}</strong></p>
            <p>Opérateur : <strong>{{ $aprovisionnement->user->full_name }}</strong></p>
            <p>Emplacement : <strong>{{ ucfirst($aprovisionnement->emplacement->nom) }}</strong></p>
        </div>
    </div>

    <!-- Tableau des articles -->
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Produit</th>
                <th class="text-center">Quantité</th>
                <th class="text-right">Prix unitaire</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($aprovisionnement->items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->product->nom }}</td>
                <td class="text-center">{{ $item->quantite }}</td>
                <td class="text-right">{{ number_format($item->prix_unitaire, 0, ',', ' ') }} FCFA</td>
                <td class="text-right">{{ number_format($item->prix_total, 0, ',', ' ') }} FCFA</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Total -->
    <div class="totals">
        <table>
            <tbody>
                <tr class="total-row">
                    <td>Montant Total</td>
                    <td>{{ number_format($aprovisionnement->montant_total, 0, ',', ' ') }} FCFA</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Note -->
    <div class="footer-note">
        Merci de bien vouloir procéder à la livraison dans les meilleurs délais et de nous confirmer la réception de cette commande.
    </div>

    <!-- Signature -->
    <div class="signature">
        <div class="signature-box">
            <div class="line">Signature & Cachet</div>
        </div>
    </div>

</body>
</html>