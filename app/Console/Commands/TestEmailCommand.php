<?php

namespace App\Console\Commands;

use App\Mail\InvoiceGenerated;
use App\Models\Facture;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test {email? : L\'adresse email de destination}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoie un email de test pour vérifier la configuration SMTP';

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
        
        $this->info('Mise en file d\'attente de l\'email de test à ' . $email . '...');
        
        try {
            // Envoyer l'email directement (sans file d'attente) pour le test
            $this->info('Envoi direct de l\'email de test à ' . $email . '...');
            Mail::to($email)->send(new InvoiceGenerated($facture));
            
            $this->info('Email envoyé avec succès!');
            $this->info('Vérifiez votre boîte de réception et vos dossiers spam/indésirables.');
            
            // Afficher les informations de configuration
            $this->table(
                ['Paramètre', 'Valeur'],
                [
                    ['QUEUE_CONNECTION', config('queue.default')],
                    ['MAIL_MAILER', config('mail.default')],
                    ['MAIL_HOST', config('mail.mailers.smtp.host')],
                    ['MAIL_PORT', config('mail.mailers.smtp.port')],
                    ['MAIL_USERNAME', config('mail.mailers.smtp.username')],
                    ['MAIL_ENCRYPTION', config('mail.mailers.smtp.encryption')],
                    ['MAIL_FROM_ADDRESS', config('mail.from.address')],
                    ['MAIL_FROM_NAME', config('mail.from.name')],
                ]
            );
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Erreur lors de l\'envoi de l\'email:');
            $this->error($e->getMessage());
            
            // Afficher des informations de débogage supplémentaires
            $this->newLine();
            $this->info('Informations de débogage:');
            $this->table(
                ['Paramètre', 'Valeur'],
                [
                    ['QUEUE_CONNECTION', config('queue.default')],
                    ['MAIL_MAILER', config('mail.default')],
                    ['MAIL_HOST', config('mail.mailers.smtp.host')],
                    ['MAIL_PORT', config('mail.mailers.smtp.port')],
                    ['MAIL_USERNAME', config('mail.mailers.smtp.username')],
                    ['MAIL_ENCRYPTION', config('mail.mailers.smtp.encryption')],
                    ['MAIL_FROM_ADDRESS', config('mail.from.address')],
                    ['MAIL_FROM_NAME', config('mail.from.name')],
                    ['Exception Type', get_class($e)],
                    ['File', $e->getFile() . ':' . $e->getLine()],
                ]
            );
            
            if ($e->getPrevious()) {
                $this->error('Erreur précédente: ' . $e->getPrevious()->getMessage());
            }
            
            return 1;
        }
    }
}