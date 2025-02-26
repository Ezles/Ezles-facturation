<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Devis;
use App\Models\LigneDevis;
use App\Models\User;
use Illuminate\Database\Seeder;

class DevisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer un client existant ou en créer un
        $client = Client::first();
        if (!$client) {
            $client = Client::create([
                'nom' => 'Client Test',
                'email' => 'client@test.com',
                'telephone' => '0123456789',
                'adresse' => '123 Rue Test',
                'code_postal' => '75000',
                'ville' => 'Paris',
                'pays' => 'France',
                'siret' => '12345678901234',
                'tva_intracom' => 'FR12345678901',
            ]);
        }

        // Récupérer un utilisateur existant
        $user = User::first();
        if (!$user) {
            $user = User::create([
                'name' => 'Admin',
                'email' => 'admin@ezles.dev',
                'password' => bcrypt('password'),
            ]);
        }

        // Créer un devis
        $devis = Devis::create([
            'numero' => Devis::genererNumero(),
            'date_emission' => now(),
            'date_validite' => now()->addDays(30),
            'statut' => 'En attente',
            'client_id' => $client->id,
            'user_id' => $user->id,
            'conditions_paiement' => 'Paiement à 30 jours',
            'notes' => 'Merci pour votre confiance.',
            'mentions_legales' => 'Ce devis est valable 30 jours à compter de sa date d\'émission.',
            'total_ht' => 1000,
            'total_tva' => 200,
            'total_ttc' => 1200,
        ]);

        // Ajouter des lignes au devis
        LigneDevis::create([
            'devis_id' => $devis->id,
            'description' => 'Développement site web',
            'quantite' => 1,
            'prix_unitaire' => 800,
            'taux_tva' => 20,
            'montant_ht' => 800,
            'montant_tva' => 160,
            'montant_ttc' => 960,
        ]);

        LigneDevis::create([
            'devis_id' => $devis->id,
            'description' => 'Hébergement annuel',
            'quantite' => 1,
            'prix_unitaire' => 200,
            'taux_tva' => 20,
            'montant_ht' => 200,
            'montant_tva' => 40,
            'montant_ttc' => 240,
        ]);

        $this->command->info('Devis créé avec succès: ' . $devis->numero);
    }
}