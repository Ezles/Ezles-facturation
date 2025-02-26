<?php

namespace App\Console\Commands;

use App\Mail\InvoiceGenerated;
use App\Models\Facture;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendCustomEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:send {invoice_number} {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoie un email avec une facture spécifique à une adresse email personnalisée';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Récupérer les arguments
        $invoiceNumber = $this->argument('invoice_number');
        $email = $this->argument('email');
        
        // Valider l'adresse email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Adresse email invalide!');
            return 1;
        }
        
        // Rechercher la facture
        $this->info('Recherche de la facture ' . $invoiceNumber . '...');
        
        $facture = Facture::with(['client', 'lignes', 'user'])
            ->where('numero', $invoiceNumber)
            ->first();
        
        if (!$facture) {
            $this->error('Facture non trouvée: ' . $invoiceNumber);
            return 1;
        }
        
        $this->info('Facture trouvée: ' . $facture->numero);
        $this->info('Client: ' . $facture->client->nom);
        $this->info('Montant: ' . number_format($facture->total_ttc, 2, ',', ' ') . ' €');
        
        $this->info('Envoi de l\'email à ' . $email . '...');
        
        try {
            // Envoyer l'email directement
            Mail::to($email)->send(new InvoiceGenerated($facture));
            
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