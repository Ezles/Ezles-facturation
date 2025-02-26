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
        }
        .header {
            background-color: #DCE8F3;
            padding: 40px;
        }
        .logo-container {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
        }
        .logo {
            height: 35px;
        }
        .logo-text {
            font-size: 24px;
            font-weight: bold;
            color: #026fe5;
            margin-left: 10px;
        }
        .title-container {
            margin-bottom: 20px;
        }
        h1 {
            color: #026fe5;
            margin: 0;
            padding: 0;
            font-size: 28px;
            line-height: 1.2;
        }
        .vertical-line {
            border-left: 2px solid #026fe5;
            height: 50px;
            margin: 20px 0;
        }
        .message-container {
            padding: 30px 40px;
            background-color: #DCE8F3;
        }
        .message {
            line-height: 1.7;
        }
        .message p {
            margin-bottom: 15px;
        }
        .invoice-details {
            background-color: #ffffff;
            padding: 25px 40px;
            border-radius: 20px 20px 0 0;
        }
        .invoice-details-table {
            width: 100%;
            border-collapse: collapse;
        }
        .invoice-details th {
            text-align: left;
            padding: 8px 0;
            color: #6b7280;
            font-weight: 500;
            font-size: 14px;
        }
        .invoice-details td {
            padding: 8px 0;
            font-weight: 600;
        }
        .amount {
            font-size: 18px;
            font-weight: 700;
            color: #026fe5;
        }
        .profile-container {
            background-color: #ffffff;
            padding: 25px 40px 15px;
            text-align: center;
        }
        .profile-image {
            width: 115px;
            border-radius: 50%;
            margin-bottom: 15px;
        }
        .profile-name {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .profile-title {
            color: #6b7280;
            margin-bottom: 15px;
        }
        .contact-container {
            background-color: #ffffff;
            padding: 0 40px 25px;
            border-radius: 0 0 20px 20px;
            text-align: center;
        }
        .contact-item {
            margin: 10px 0;
            color: #00356c;
        }
        .footer {
            padding: 40px 30px;
            text-align: center;
            color: #6b7280;
            font-size: 13px;
        }
        .footer p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header Section -->
        <div class="header">
            <div class="logo-container">
                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('ezles-freelance-logo.png'))) }}" alt="Ezles" class="logo">
                <div class="logo-text">Ezles</div>
            </div>
            <div class="title-container">
                <h1>Facture</h1>
                <h1>Confirmation -</h1>
                <h1>Merci !</h1>
            </div>
            <div class="vertical-line"></div>
        </div>
        
        <!-- Message Section -->
        <div class="message-container">
            <div class="message">
                <p><strong>Cher(e) {{ $facture->client->nom }},</strong></p>
                
                <p>J'espère que ce message vous trouve bien. Je tenais à vous remercier personnellement pour votre paiement rapide ; il a été reçu avec succès et crédité sur mon compte.</p>
                
                <p>Votre soutien et votre confiance dans mes services signifient beaucoup pour moi, et je suis ravi de continuer à vous fournir des prestations de qualité pour vos projets. Si vous avez des missions à venir ou besoin d'assistance, n'hésitez pas à me contacter. Je suis là pour vous aider.</p>
                
                <p>Encore une fois, merci pour votre paiement ponctuel et pour m'avoir choisi comme prestataire. J'attends avec impatience notre collaboration continue.</p>
                
                <p>Cordialement,<br><br><br>
                <strong>L'équipe Ezles</strong></p>
            </div>
        </div>
        
        <!-- Invoice Details Section -->
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
                    <th>Date d'échéance:</th>
                    <td>{{ $facture->date_echeance->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <th>Montant total:</th>
                    <td class="amount">{{ number_format($facture->total_ttc, 2, ',', ' ') }} €</td>
                </tr>
                <tr>
                    <th>Statut:</th>
                    <td>
                        @if($facture->statut == 'Payée')
                            <span style="color: #10b981;">Payée</span>
                        @elseif($facture->statut == 'En attente')
                            <span style="color: #f59e0b;">En attente de paiement</span>
                        @else
                            <span style="color: #ef4444;">En retard</span>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Profile Section -->
        <div class="profile-container">
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('ezles-freelance-logo.png'))) }}" alt="Ezles" class="profile-image">
            <div class="profile-name">Ezles</div>
            <div class="profile-title">Services Professionnels</div>
        </div>
        
        <!-- Contact Section -->
        <div class="contact-container">
            <div class="contact-item">
                <a href="mailto:contact@ezles.dev" style="color: #00356c; text-decoration: none;">contact@ezles.dev</a>
            </div>
        </div>
        
        <!-- Footer Section -->
        <div class="footer">
            <p>© {{ date('Y') }} Ezles - Tous droits réservés</p>
        </div>
    </div>
</body>
</html> 