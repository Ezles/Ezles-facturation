<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class StartQueueWorkerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:start {--timeout=60 : Le nombre de secondes qu\'un job peut s\'exécuter}
                           {--tries=3 : Le nombre de fois qu\'un job peut être tenté}
                           {--sleep=3 : Le nombre de secondes à attendre avant de vérifier les nouveaux jobs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Démarre le worker de file d\'attente en arrière-plan';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $timeout = $this->option('timeout');
        $tries = $this->option('tries');
        $sleep = $this->option('sleep');
        
        $command = 'nohup php artisan queue:work --timeout=' . $timeout . ' --tries=' . $tries . ' --sleep=' . $sleep . ' > storage/logs/queue-worker.log 2>&1 &';
        
        $this->info('Démarrage du worker de file d\'attente en arrière-plan...');
        
        exec($command, $output, $returnVar);
        
        if ($returnVar === 0) {
            $this->info('Le worker de file d\'attente a été démarré avec succès!');
            $this->info('Les logs sont disponibles dans storage/logs/queue-worker.log');
            
            // Vérifier si le processus est en cours d'exécution
            exec('ps aux | grep "[q]ueue:work"', $psOutput);
            
            if (count($psOutput) > 0) {
                $this->info('Processus en cours d\'exécution:');
                $this->line(implode("\n", $psOutput));
            } else {
                $this->warn('Aucun processus queue:work n\'a été trouvé. Vérifiez les logs pour plus d\'informations.');
            }
            
            return 0;
        } else {
            $this->error('Une erreur est survenue lors du démarrage du worker de file d\'attente.');
            $this->error('Sortie: ' . implode("\n", $output));
            return 1;
        }
    }
} 