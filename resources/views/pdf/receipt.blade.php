<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçu de paiement {{ $facture->numero }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 15px;
        }
        .header {
            border: 1px solid #6c5ce7;
            border-radius: 5px;
            padding: 10px 15px 10px 0;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo-container {
            width: auto;
            text-align: left;
            margin-right: auto;
            padding: 0;
            margin-left: -10px;
        }

        .logo {
            max-height: 60px;
            max-width: 100%;
            display: block;
            margin-left: 0;
        }

        .company-info {
            text-align: right;
            font-size: 11px;
            line-height: 1.5;
        }

        .document-title {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 8px;
        }
        
        .document-info {
            margin-bottom: 20px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .info-col {
            width: 48%;
        }
        .info-label {
            font-weight: bold;
            margin-bottom: 2px;
        }
        .info-value {
            font-size: 10px;
        }
        .client-info {
            margin-bottom: 20px;
        }
        .client-title {
            font-weight: bold;
            margin-bottom: 3px;
        }
        .payment-confirmation {
            background-color: #e6f7ff;
            border: 1px solid #91d5ff;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        .payment-confirmation-title {
            font-size: 12px;
            font-weight: bold;
            color: #1890ff;
            margin-bottom: 3px;
        }
        .payment-confirmation-text {
            font-size: 9px;
        }
        .items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .items th {
            background-color: #f8f9fa;
            padding: 6px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .items td {
            padding: 6px;
            border-bottom: 1px solid #eee;
        }
        .items tr:last-child td {
            border-bottom: none;
        }
        .text-right {
            text-align: right;
        }
        .totals {
            margin-left: auto;
            width: 40%;
            margin-bottom: 25px;
        }
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 5px 0;
        }
        .label {
            text-align: left;
        }
        .value {
            text-align: right;
            font-weight: bold;
        }
        .total-row {
            font-size: 12px;
            font-weight: bold;
            border-top: 1px solid #ddd;
        }
        .total-row td {
            padding-top: 10px;
        }
        .notes {
            margin-bottom: 20px;
            font-size: 9px;
        }
        .notes-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .footer {
            text-align: center;
            font-size: 8px;
            color: #777;
            margin-top: 15px;
            border-top: 1px solid #eee;
            padding-top: 8px;
        }
        .payment-details {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 20px;
        }
        .payment-details-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .payment-details-table {
            width: 100%;
            border-collapse: collapse;
        }
        .payment-details-table td {
            padding: 5px 0;
            font-size: 9px;
        }
        .payment-details-table .label {
            width: 40%;
            font-weight: bold;
        }
        .stamp {
            position: absolute;
            top: 300px;
            right: 100px;
            transform: rotate(15deg);
            color: rgba(76, 175, 80, 0.5);
            border: 3px solid rgba(76, 175, 80, 0.5);
            border-radius: 10px;
            padding: 10px;
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo-container">
                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('Ezles DEV-1000x1000.png'))) }}" alt="Ezles" class="logo">
            </div>
            <div class="company-info">
                <div>123 Anywhere St., Any City, ST 12345</div>
                <div>123-456-7890</div>
                <div>www.ezles.dev</div>
            </div>
        </div>

        <div class="document-title">Reçu de paiement</div>

        <div class="document-info">
            <div class="info-row">
                <div class="info-col">
                    <div class="info-label">Facture #:</div>
                    <div class="info-value">{{ $facture->numero }}</div>
                </div>
                <div class="info-col">
                    <div class="info-label">Date de paiement:</div>
                    <div class="info-value">{{ $facture->date_paiement ? $facture->date_paiement->format('d/m/Y') : date('d/m/Y') }}</div>
                </div>
            </div>
            <div class="info-row">
                <div class="info-col">
                    <div class="info-label">Date d'émission:</div>
                    <div class="info-value">{{ $facture->date_emission->format('d/m/Y') }}</div>
                </div>
                <div class="info-col">
                    <div class="info-label">Référence de paiement:</div>
                    <div class="info-value">REF-{{ $facture->numero }}-{{ date('Ymd') }}</div>
                </div>
            </div>
        </div>

        <div class="client-info">
            <div class="client-title">Client:</div>
            <div>{{ $facture->client->nom }}</div>
            @if($facture->client->email)
            <div>{{ $facture->client->email }}</div>
            @endif
            <div>{{ $facture->client->adresse }}</div>
            <div>
                @if($facture->client->code_postal){{ $facture->client->code_postal }}@endif
                @if($facture->client->ville){{ $facture->client->ville }}@endif
            </div>
            @if($facture->client->siret)
            <div>SIRET: {{ $facture->client->siret }}</div>
            @endif
        </div>

        <div class="payment-confirmation">
            <div class="payment-confirmation-title">Paiement reçu</div>
            <div class="payment-confirmation-text">Paiement confirmé pour la facture {{ $facture->numero }}</div>
        </div>

        <div class="payment-details">
            <div class="payment-details-title">Détails du paiement</div>
            <table class="payment-details-table">
                <tr>
                    <td class="label">Mode de paiement:</td>
                    <td>{{ $facture->mode_paiement }}</td>
                </tr>
                <tr>
                    <td class="label">Date de paiement:</td>
                    <td>{{ $facture->date_paiement ? $facture->date_paiement->format('d/m/Y') : date('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Montant payé:</td>
                    <td>{{ number_format($facture->total_ttc, 2, ',', ' ') }} €</td>
                </tr>
                <tr>
                    <td class="label">Statut:</td>
                    <td>Payé</td>
                </tr>
            </table>
        </div>

        <table class="items">
            <thead>
                <tr>
                    <th width="40%">Description</th>
                    <th width="15%">Quantité</th>
                    <th width="15%">Prix unitaire</th>
                    <th width="10%">TVA</th>
                    <th width="20%">Total HT</th>
                </tr>
            </thead>
            <tbody>
                @foreach($facture->lignes as $ligne)
                <tr>
                    <td>{{ $ligne->description }}</td>
                    <td>{{ number_format($ligne->quantite, 2, ',', ' ') }}</td>
                    <td>{{ number_format($ligne->prix_unitaire, 2, ',', ' ') }} €</td>
                    <td>{{ number_format($ligne->taux_tva, 2, ',', ' ') }}%</td>
                    <td class="text-right">{{ number_format($ligne->montant_ht, 2, ',', ' ') }} €</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <table class="totals-table">
                <tr>
                    <td class="label">Total HT</td>
                    <td class="value">{{ number_format($facture->total_ht, 2, ',', ' ') }} €</td>
                </tr>
                <tr>
                    <td class="label">TVA</td>
                    <td class="value">{{ number_format($facture->total_tva, 2, ',', ' ') }} €</td>
                </tr>
                <tr class="total-row">
                    <td class="label">Total TTC</td>
                    <td class="value">{{ number_format($facture->total_ttc, 2, ',', ' ') }} €</td>
                </tr>
            </table>
        </div>

        <div class="stamp">Payé</div>

        <div class="footer">
            <p>© Ezles - {{ date('Y') }}</p>
            <p style="margin-top: 5px; font-style: italic;">Ce document est un reçu de paiement et atteste du règlement intégral de la facture mentionnée. À conserver comme justificatif de paiement.</p>
        </div>
    </div>
</body>
</html> 