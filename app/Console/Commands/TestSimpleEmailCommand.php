<?php

namespace App\Console\Commands;

use App\Models\Facture;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestSimpleEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test-simple {email? : L\'adresse email de destination}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoie un email de test avec un template simplifié';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Récupérer l'adresse email de destination
        $email = $this->argument('email');
        
        // Si aucune adresse email n'est fournie, demander à l'utilisateur
        if (!$email) {
            $email = $this->ask('À quelle adresse email souhaitez-vous envoyer le test?');
        }
        
        // Valider l'adresse email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Adresse email invalide!');
            return 1;
        }
        
        $this->info('Recherche d\'une facture pour le test...');
        
        // Récupérer une facture pour le test
        $facture = Facture::with(['client', 'lignes', 'user'])->first();
        
        if (!$facture) {
            $this->error('Aucune facture trouvée dans la base de données pour le test.');
            return 1;
        }
        
        $this->info('Envoi de l\'email de test à ' . $email . '...');
        
        try {
            // Envoyer l'email directement (sans file d'attente) avec le template simplifié
            Mail::send('emails.simple-invoice', ['facture' => $facture], function ($message) use ($email, $facture) {
                $message->to($email)
                    ->subject('Facture ' . $facture->numero . ' - Ezles (Template Simplifié)');
            });
            
            $this->info('Email envoyé avec succès!');
            $this->info('Vérifiez votre boîte de réception et vos dossiers spam/indésirables.');
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Erreur lors de l\'envoi de l\'email:');
            $this->error($e->getMessage());
            
            if ($e->getPrevious()) {
                $this->error('Erreur précédente: ' . $e->getPrevious()->getMessage());
            }
            
            return 1;
        }
    }
} 