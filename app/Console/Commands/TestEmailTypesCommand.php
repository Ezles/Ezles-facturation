<?php

namespace App\Console\Commands;

use App\Mail\InvoiceGenerated;
use App\Mail\InvoicePaid;
use App\Mail\InvoiceThanks;
use App\Mail\QuoteGenerated;
use App\Models\Facture;
use App\Models\Devis;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmailTypesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test-types {email} {type=all : Type d\'email à tester (invoice, receipt, thanks, quote, all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Teste l\'envoi des différents types d\'emails (facture, reçu, remerciement, devis)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Récupérer les arguments
        $email = $this->argument('email');
        $type = $this->argument('type');
        
        // Valider l'adresse email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Adresse email invalide!');
            return 1;
        }
        
        // Rechercher une facture pour le test
        $this->info('Recherche d\'une facture pour le test...');
        
        $facture = Facture::with(['client', 'lignes', 'user'])
            ->first();
        
        if (!$facture && ($type === 'invoice' || $type === 'receipt' || $type === 'thanks' || $type === 'all')) {
            $this->error('Aucune facture trouvée dans la base de données.');
            return 1;
        }
        
        if ($facture) {
            $this->info('Facture trouvée: ' . $facture->numero);
            $this->info('Client: ' . $facture->client->nom);
            $this->info('Montant: ' . number_format($facture->total_ttc, 2, ',', ' ') . ' €');
        }
        
        // Rechercher un devis pour le test
        if ($type === 'quote' || $type === 'all') {
            $this->info('Recherche d\'un devis pour le test...');
            
            $devis = Devis::with(['client', 'lignes', 'user'])
                ->first();
            
            if (!$devis) {
                $this->error('Aucun devis trouvé dans la base de données.');
                if ($type === 'quote') {
                    return 1;
                }
            } else {
                $this->info('Devis trouvé: ' . $devis->numero);
                $this->info('Client: ' . $devis->client->nom);
                $this->info('Montant: ' . number_format($devis->total_ttc, 2, ',', ' ') . ' €');
            }
        }
        
        // Envoyer les emails selon le type demandé
        if ($type === 'invoice' || $type === 'all') {
            $this->info('Envoi de l\'email de facture à ' . $email . '...');
            Mail::to($email)->send(new InvoiceGenerated($facture));
            $this->info('Email de facture envoyé avec succès!');
        }
        
        if ($type === 'receipt' || $type === 'all') {
            $this->info('Envoi de l\'email de reçu de paiement à ' . $email . '...');
            Mail::to($email)->send(new InvoicePaid($facture));
            $this->info('Email de reçu de paiement envoyé avec succès!');
        }
        
        if ($type === 'thanks' || $type === 'all') {
            $this->info('Envoi de l\'email de remerciement à ' . $email . '...');
            Mail::to($email)->send(new InvoiceThanks($facture));
            $this->info('Email de remerciement envoyé avec succès!');
        }
        
        if (($type === 'quote' || $type === 'all') && isset($devis)) {
            $this->info('Envoi de l\'email de devis à ' . $email . '...');
            Mail::to($email)->send(new QuoteGenerated($devis));
            $this->info('Email de devis envoyé avec succès!');
        }
        
        $this->info('Vérifiez votre boîte de réception et vos dossiers de spam pour les emails.');
        
        // Afficher les paramètres SMTP actuels
        $this->info('Paramètres SMTP actuels:');
        $this->info('QUEUE_CONNECTION: ' . config('queue.default'));
        $this->info('MAIL_MAILER: ' . config('mail.default'));
        $this->info('MAIL_HOST: ' . config('mail.mailers.smtp.host'));
        $this->info('MAIL_PORT: ' . config('mail.mailers.smtp.port'));
        $this->info('MAIL_USERNAME: ' . config('mail.mailers.smtp.username'));
        $this->info('MAIL_ENCRYPTION: ' . config('mail.mailers.smtp.encryption'));
        $this->info('MAIL_FROM_ADDRESS: ' . config('mail.from.address'));
        $this->info('MAIL_FROM_NAME: ' . config('mail.from.name'));
        
        return 0;
    }
} 