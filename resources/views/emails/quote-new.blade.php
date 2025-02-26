<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Devis {{ $devis->numero }} - Ezles</title>
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
            background-color: #f0f7ff;
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
            color: #026fe5;
            margin: 0 0 20px 0;
            font-size: 24px;
        }
        h2 {
            color: #026fe5;
            font-size: 18px;
            margin: 25px 0 15px 0;
        }
        p {
            margin-bottom: 15px;
        }
        .quote-details {
            background-color: #f9fafb;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .quote-details-table {
            width: 100%;
            border-collapse: collapse;
        }
        .quote-details-table th {
            text-align: left;
            padding: 8px 0;
            color: #6b7280;
            font-weight: 500;
            width: 40%;
        }
        .quote-details-table td {
            padding: 8px 0;
            font-weight: 600;
        }
        .amount {
            font-size: 18px;
            font-weight: 700;
            color: #026fe5;
        }
        .info-box {
            background-color: #f0f7ff;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #026fe5;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #6b7280;
            font-size: 13px;
            margin-top: 20px;
        }
        .button {
            display: inline-block;
            background-color: #026fe5;
            color: white;
            text-decoration: none;
            padding: 12px 25px;
            border-radius: 5px;
            font-weight: bold;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('Ezles DEV-1000x1000.png'))) }}" alt="Ezles" class="logo">
            <h1>Devis {{ $devis->numero }}</h1>
        </div>
        
        <div class="content">
            <p>Bonjour {{ $devis->client->nom }},</p>
            
            <div class="info-box">
                <p>Suite à notre échange, veuillez trouver ci-joint notre proposition commerciale détaillée pour les prestations demandées.</p>
                <p>Ce devis est valable pour une durée de {{ $devis->validite ?? '30' }} jours à compter de sa date d'émission.</p>
            </div>
            
            <div class="quote-details">
                <table class="quote-details-table">
                    <tr>
                        <th>Référence:</th>
                        <td>{{ $devis->numero }}</td>
                    </tr>
                    <tr>
                        <th>Date d'émission:</th>
                        <td>{{ $devis->date_emission->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <th>Date de validité:</th>
                        <td>{{ $devis->date_validite ? $devis->date_validite->format('d/m/Y') : $devis->date_emission->addDays(30)->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <th>Montant total:</th>
                        <td class="amount">{{ number_format($devis->total_ttc, 2, ',', ' ') }} €</td>
                    </tr>
                </table>
            </div>
            
            <h2>Prochaines étapes</h2>
            
            <div class="info-box">
                <p>Pour accepter ce devis, vous pouvez :</p>
                <ol>
                    <li>Nous retourner le devis signé par email</li>
                    <li>Nous confirmer votre accord par retour d'email</li>
                </ol>
                <p>Dès réception de votre accord, nous établirons un planning de réalisation et vous proposerons une date de démarrage pour votre projet.</p>
            </div>
            
            <p>N'hésitez pas à nous contacter pour toute question ou précision concernant cette proposition.</p>
            
            <p>Cordialement,<br>
            <strong>L'équipe Ezles</strong></p>
        </div>
        
        <div class="footer">
            <p>
                <a href="mailto:contact@ezles.dev" style="color: #026fe5; text-decoration: none;">contact@ezles.dev</a>
            </p>
            <p>© {{ date('Y') }} Ezles - Tous droits réservés</p>
        </div>
    </div>
</body>
</html> 