<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Devis {{ $devis->numero }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            line-height: 1.4;
            margin: 0;
            padding: 0;
            font-size: 12px;
        }
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            position: relative; /* Pour le positionnement absolu du logo */
        }
        .logo-container {
            position: absolute;
            top: 0;
            right: 0;
            z-index: 100;
        }
        .logo {
            max-width: 120px;
            max-height: 60px;
        }
        .header {
            width: 100%;
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            padding-top: 70px; /* Espace pour le logo */
        }
        .header-left {
            width: 100%;
        }
        .company-info {
            line-height: 1.5;
        }
        .document-title-container {
            width: 100%;
            text-align: center;
            margin: 20px 0 30px 0;
        }
        .document-title {
            font-size: 28px;
            font-weight: bold;
            color: #026fe5;
            text-transform: uppercase;
            letter-spacing: 3px;
            display: inline-block;
            padding: 5px 30px;
            border-bottom: 2px solid #026fe5;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .info-table td {
            padding: 4px 0;
        }
        .info-table td:first-child {
            font-weight: bold;
            width: 40%;
            color: #555;
        }
        .info-table td:last-child {
            font-weight: 600;
        }
        .info-block {
            margin-bottom: 25px;
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #026fe5;
        }
        .info-block h3 {
            font-size: 14px;
            margin: 0 0 10px 0;
            color: #026fe5;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .client-info {
            line-height: 1.6;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
        }
        .items-table th {
            background-color: #f0f7ff;
            border-bottom: 2px solid #026fe5;
            padding: 10px;
            text-align: left;
            color: #026fe5;
            font-weight: 600;
        }
        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .items-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .items-table .amount {
            text-align: right;
        }
        .items-table .description {
            font-weight: 500;
            color: #333;
        }
        .total-table {
            width: 40%;
            margin-left: auto;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .total-table td {
            padding: 8px 10px;
        }
        .total-table .label {
            text-align: left;
            font-weight: 600;
            color: #555;
        }
        .total-table .amount {
            text-align: right;
            font-weight: bold;
        }
        .total-table .total-row {
            font-size: 16px;
            font-weight: bold;
            background-color: #f0f7ff;
            color: #026fe5;
        }
        .validity {
            margin: 25px 0;
            padding: 12px 15px;
            background-color: #f0f7ff;
            border-left: 4px solid #026fe5;
            font-weight: bold;
            color: #026fe5;
            text-align: center;
            border-radius: 0 5px 5px 0;
        }
        .payment-terms {
            margin-top: 25px;
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #026fe5;
        }
        .payment-terms h3 {
            margin-top: 0;
            color: #026fe5;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 14px;
        }
        .notes {
            margin-top: 25px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
            border-left: 4px solid #026fe5;
        }
        .notes h3 {
            margin-top: 0;
            color: #026fe5;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 14px;
        }
        .signature-block {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px dashed #ccc;
        }
        .signature-table {
            width: 100%;
            margin-top: 20px;
        }
        .signature-cell {
            width: 50%;
            vertical-align: top;
            padding: 10px;
        }
        .signature-line {
            margin-top: 50px;
            border-top: 1px solid #333;
            width: 70%;
        }
        .signature-text {
            font-size: 10px;
            color: #777;
            margin-top: 5px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #eee;
            text-align: center;
            font-size: 11px;
            color: #777;
        }
        .invoice-details {
            margin-top: 20px;
            width: 100%;
            border-collapse: collapse;
        }
        .invoice-details td {
            padding: 4px 0;
        }
        .invoice-details td:first-child {
            font-weight: bold;
            width: 30%;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Logo positionné absolument en haut à droite -->
        <div class="logo-container">
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('Ezles DEV-1000x1000.png'))) }}" alt="Logo" class="logo">
        </div>

        <div class="header">
            <div class="header-left">
                <div class="company-info">
                    <strong>Ezles</strong><br>
                    123 Rue de l'Innovation<br>
                    75000 Paris<br>
                    France<br>
                    Email: contact@ezles.dev<br>
                    SIRET: 123 456 789 00012
                </div>
                
                <!-- Informations du devis -->
                <table class="invoice-details">
                    <tr>
                        <td>Numéro:</td>
                        <td>{{ $devis->numero }}</td>
                    </tr>
                    <tr>
                        <td>Date d'émission:</td>
                        <td>{{ $devis->date_emission->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td>Date de validité:</td>
                        <td>{{ $devis->date_validite->format('d/m/Y') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="document-title-container">
            <div class="document-title">DEVIS</div>
        </div>

        <div class="info-block">
            <h3>CLIENT</h3>
            <div class="client-info">
                <strong>{{ $devis->client->nom }}</strong><br>
                {{ $devis->client->adresse }}<br>
                {{ $devis->client->code_postal }} {{ $devis->client->ville }}<br>
                {{ $devis->client->pays }}<br>
                @if($devis->client->email)
                Email: {{ $devis->client->email }}<br>
                @endif
                @if($devis->client->telephone)
                Tél: {{ $devis->client->telephone }}<br>
                @endif
                @if($devis->client->siret)
                SIRET: {{ $devis->client->siret }}
                @endif
            </div>
        </div>

        <div class="validity">
            Ce devis est valable jusqu'au {{ $devis->date_validite->format('d/m/Y') }}
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 50%;">Description</th>
                    <th style="width: 10%;">Quantité</th>
                    <th style="width: 15%;">Prix unitaire</th>
                    <th style="width: 10%;">TVA</th>
                    <th style="width: 15%;">Total HT</th>
                </tr>
            </thead>
            <tbody>
                @foreach($devis->lignes as $ligne)
                <tr>
                    <td class="description">{{ $ligne->description }}</td>
                    <td>{{ number_format($ligne->quantite, 2, ',', ' ') }}</td>
                    <td class="amount">{{ number_format($ligne->prix_unitaire, 2, ',', ' ') }} €</td>
                    <td class="amount">{{ number_format($ligne->taux_tva, 2, ',', ' ') }}%</td>
                    <td class="amount">{{ number_format($ligne->montant_ht, 2, ',', ' ') }} €</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table class="total-table">
            <tr>
                <td class="label">Total HT</td>
                <td class="amount">{{ number_format($devis->total_ht, 2, ',', ' ') }} €</td>
            </tr>
            <tr>
                <td class="label">TVA</td>
                <td class="amount">{{ number_format($devis->total_tva, 2, ',', ' ') }} €</td>
            </tr>
            <tr class="total-row">
                <td class="label">Total TTC</td>
                <td class="amount">{{ number_format($devis->total_ttc, 2, ',', ' ') }} €</td>
            </tr>
        </table>

        @if($devis->conditions_paiement)
        <div class="payment-terms">
            <h3>Conditions de paiement</h3>
            <p>{{ $devis->conditions_paiement }}</p>
        </div>
        @endif

        @if($devis->notes)
        <div class="notes">
            <h3>Notes</h3>
            <p>{{ $devis->notes }}</p>
        </div>
        @endif

        <div class="signature-block">
            <p>Pour accepter ce devis, veuillez le retourner signé avec la mention "Bon pour accord".</p>
            
            <table class="signature-table">
                <tr>
                    <td class="signature-cell">
                        <p><strong>Pour Ezles</strong></p>
                        <p>Nom et fonction du signataire:</p>
                        <p>{{ $devis->user->name }}</p>
                    </td>
                    <td class="signature-cell">
                        <p><strong>Pour {{ $devis->client->nom }}</strong></p>
                        <p>Nom et fonction du signataire:</p>
                        <div class="signature-line"></div>
                        <p class="signature-text">Signature précédée de la mention "Bon pour accord"</p>
                    </td>
                </tr>
            </table>
        </div>

        <div class="footer">
            @if($devis->mentions_legales)
            <p>{{ $devis->mentions_legales }}</p>
            @else
            <p>Ce devis est valable 30 jours à compter de sa date d'émission.</p>
            <p>Ezles - SIRET: 123 456 789 00012 - TVA Intracommunautaire: FR12345678900</p>
            @endif
        </div>
    </div>
</body>
</html> 