<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Devis Simple</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
    </style>
</head>
<body>
    <h1>Devis Test</h1>
    <p>Numéro: {{ $devis->numero }}</p>
    <p>Date: {{ $devis->date_emission ? $devis->date_emission->format('d/m/Y') : 'N/A' }}</p>
    <p>Client: {{ $devis->client ? $devis->client->nom : 'Client non défini' }}</p>
    <p>Total: {{ number_format($devis->total_ttc, 2, ',', ' ') }} €</p>
</body>
</html> 