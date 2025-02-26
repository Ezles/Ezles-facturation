<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Facture {{ $facture->numero }} - Ezles</title>
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
            border: 1px solid #ddd;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 15px;
        }
        .details {
            margin-bottom: 20px;
        }
        .invoice-details {
            background-color: #f9fafb;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #777;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }
        h1 {
            color: #026fe5;
            margin: 0 0 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('Ezles DEV-1000x1000.png'))) }}" alt="Ezles" class="logo">
            <h1>Facture {{ $facture->numero }}</h1>
            <p>Ezles - Services Professionnels</p>
        </div>
        
        <div class="details">
            <p><strong>Cher(e) {{ $facture->client->nom }},</strong></p>
            
            <p>Voici votre facture d'un montant de {{ number_format($facture->total_ttc, 2, ',', ' ') }} €.</p>
            
            <div class="invoice-details">
                <p><strong>Détails de la facture :</strong></p>
                <ul>
                    <li>Numéro : {{ $facture->numero }}</li>
                    <li>Date d'émission : {{ $facture->date_emission->format('d/m/Y') }}</li>
                    <li>Date d'échéance : {{ $facture->date_echeance->format('d/m/Y') }}</li>
                    <li>Statut : 
                        @if($facture->statut == 'Payée')
                            <span style="color: #10b981;">Payée</span>
                        @elseif($facture->statut == 'En attente')
                            <span style="color: #f59e0b;">En attente de paiement</span>
                        @else
                            <span style="color: #ef4444;">En retard</span>
                        @endif
                    </li>
                </ul>
            </div>
            
            <p>Merci pour votre confiance.</p>
            
            <p>Cordialement,<br>L'équipe Ezles</p>
        </div>
        
        <div class="footer">
            <p>© {{ date('Y') }} Ezles - Tous droits réservés</p>
            <p><a href="mailto:contact@ezles.dev" style="color: #026fe5; text-decoration: none;">contact@ezles.dev</a> | +33 1 23 45 67 89</p>
        </div>
    </div>
</body>
</html> 