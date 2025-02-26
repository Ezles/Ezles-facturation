<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestSmtpCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test-smtp {email? : L\'adresse email de destination}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Teste la connexion SMTP en envoyant un email simple';

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
        
        $this->info('Affichage de la configuration SMTP actuelle:');
        $this->table(
            ['Paramètre', 'Valeur'],
            [
                ['MAIL_MAILER', config('mail.default')],
                ['MAIL_HOST', config('mail.mailers.smtp.host')],
                ['MAIL_PORT', config('mail.mailers.smtp.port')],
                ['MAIL_USERNAME', config('mail.mailers.smtp.username')],
                ['MAIL_ENCRYPTION', config('mail.mailers.smtp.encryption')],
                ['MAIL_FROM_ADDRESS', config('mail.from.address')],
                ['MAIL_FROM_NAME', config('mail.from.name')],
            ]
        );
        
        $this->info('Tentative de connexion au serveur SMTP...');
        
        try {
            // Envoyer un email simple
            Mail::raw('Ceci est un test de connexion SMTP depuis l\'application Ezles Facturation.', function ($message) use ($email) {
                $message->to($email)
                    ->subject('Test de connexion SMTP - Ezles Facturation');
            });
            
            $this->info('Email de test envoyé avec succès à ' . $email);
            $this->info('Vérifiez votre boîte de réception et vos dossiers spam/indésirables.');
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Erreur lors de la connexion au serveur SMTP:');
            $this->error($e->getMessage());
            
            if ($e->getPrevious()) {
                $this->error('Erreur précédente: ' . $e->getPrevious()->getMessage());
            }
            
            $this->newLine();
            $this->info('Suggestions de dépannage:');
            $this->line('1. Vérifiez que les informations SMTP dans votre fichier .env sont correctes');
            $this->line('2. Assurez-vous que le serveur SMTP est accessible depuis votre environnement');
            $this->line('3. Vérifiez que le port SMTP n\'est pas bloqué par un pare-feu');
            $this->line('4. Si vous utilisez Gmail, assurez-vous d\'avoir activé l\'accès aux applications moins sécurisées ou créé un mot de passe d\'application');
            
            return 1;
        }
    }
} 