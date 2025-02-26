<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Paiement reçu - Facture {{ $facture->numero }} - Ezles</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f9fafb;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
        }
        .header {
            background-color: #e6f7ee;
            padding: 30px;
            text-align: center;
        }
        .logo {
            height: 100px;
            margin-bottom: 20px;
        }
        .content {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 5px;
            margin-top: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        h1 {
            color: #10b981;
            margin: 0 0 20px 0;
            font-size: 24px;
        }
        p {
            margin-bottom: 15px;
        }
        .invoice-details {
            background-color: #f9fafb;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .invoice-details-table {
            width: 100%;
            border-collapse: collapse;
        }
        .invoice-details-table th {
            text-align: left;
            padding: 8px 0;
            color: #6b7280;
            font-weight: 500;
            width: 40%;
        }
        .invoice-details-table td {
            padding: 8px 0;
            font-weight: 600;
        }
        .amount {
            font-size: 18px;
            font-weight: 700;
            color: #10b981;
        }
        .confirmation-box {
            background-color: #e6f7ee;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #10b981;
            text-align: center;
        }
        .confirmation-icon {
            font-size: 48px;
            color: #10b981;
            margin-bottom: 10px;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #6b7280;
            font-size: 13px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('Ezles DEV-1000x1000.png'))) }}" alt="Ezles" class="logo">
            <h1>Paiement reçu</h1>
        </div>
        
        <div class="content">
            <p>Bonjour {{ $facture->client->nom }},</p>
            
            <div class="confirmation-box">
                <div class="confirmation-icon">✓</div>
                <p><strong>Nous avons bien reçu votre paiement pour la facture {{ $facture->numero }}.</strong></p>
                <p>Merci pour votre règlement.</p>
            </div>
            
            <div class="invoice-details">
                <table class="invoice-details-table">
                    <tr>
                        <th>Référence:</th>
                        <td>{{ $facture->numero }}</td>
                    </tr>
                    <tr>
                        <th>Date d'émission:</th>
                        <td>{{ $facture->date_emission->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <th>Date de paiement:</th>
                        <td>{{ now()->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <th>Montant réglé:</th>
                        <td class="amount">{{ number_format($facture->total_ttc, 2, ',', ' ') }} €</td>
                    </tr>
                </table>
            </div>
            
            <p>Votre facture est désormais marquée comme <strong>payée</strong>.</p>
            
            <p>Notre équipe est maintenant pleinement mobilisée sur votre projet et a commencé à travailler sur le/les prestation(s) demandée(s). Nous vous tiendrons informé de l'avancement et répondrons à vos questions dans les meilleurs délais.</p>
            
            <p>Cordialement,<br>
            <strong>L'équipe Ezles</strong></p>
        </div>
        
        <div class="footer">
            <p>
                <a href="mailto:contact@ezles.dev" style="color: #10b981; text-decoration: none;">contact@ezles.dev</a>
            </p>
            <p>© {{ date('Y') }} Ezles - Tous droits réservés</p>
        </div>
    </div>
</body>
</html> 