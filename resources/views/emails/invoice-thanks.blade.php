<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Merci pour votre confiance - Ezles</title>
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
        p {
            margin-bottom: 15px;
        }
        .project-details {
            background-color: #f9fafb;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .thank-you-box {
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
        .feedback-section {
            text-align: center;
            margin: 25px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('Ezles DEV-1000x1000.png'))) }}" alt="Ezles" class="logo">
            <h1>Merci pour votre confiance</h1>
        </div>
        
        <div class="content">
            <p>Bonjour {{ $facture->client->nom }},</p>
            
            <div class="thank-you-box">
                <p>Nous tenons à vous remercier sincèrement pour votre confiance. La prestation concernant la facture <strong>{{ $facture->numero }}</strong> est maintenant <strong>terminée et livrée</strong>. Nous espérons que le résultat final correspond parfaitement à vos attentes et aux objectifs fixés.</p>
            </div>
            
            <div class="project-details">
                <p><strong>Projet :</strong> {{ $facture->lignes->first()->description ?? 'Prestation de services' }}</p>
                <p><strong>Facture :</strong> {{ $facture->numero }}</p>
                <p><strong>Montant :</strong> {{ number_format($facture->total_ttc, 2, ',', ' ') }} €</p>
                <p><strong>Statut :</strong> <span style="color: #10b981;">Terminé et livré</span></p>
            </div>
            
            <p>Ce fut un plaisir de collaborer avec vous sur ce projet. Nous restons à votre disposition pour toute question concernant la livraison ou pour d'éventuels ajustements mineurs dans le cadre de notre garantie de satisfaction.</p>
            
            <div class="feedback-section">
                <p>Votre satisfaction est notre priorité. N'hésitez pas à nous faire part de vos retours :</p>
                <a href="mailto:contact@ezles.dev?subject=Feedback%20sur%20la%20prestation%20{{ $facture->numero }}" class="button">Donner mon avis</a>
            </div>
            
            <p>Si vous avez besoin d'assistance ou de précisions concernant la prestation réalisée, n'hésitez pas à nous contacter.</p>
            
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