<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class StopQueueWorkerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:stop';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Arrête tous les workers de file d\'attente en cours d\'exécution';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Recherche des processus queue:work en cours d\'exécution...');
        
        // Trouver tous les processus queue:work
        exec('ps aux | grep "[q]ueue:work"', $processes);
        
        if (count($processes) === 0) {
            $this->warn('Aucun processus queue:work n\'a été trouvé.');
            return 0;
        }
        
        $this->info('Processus trouvés:');
        $this->line(implode("\n", $processes));
        
        // Extraire les PIDs
        $pids = [];
        foreach ($processes as $process) {
            $parts = preg_split('/\s+/', trim($process));
            if (isset($parts[1]) && is_numeric($parts[1])) {
                $pids[] = $parts[1];
            }
        }
        
        if (count($pids) === 0) {
            $this->warn('Impossible d\'extraire les PIDs des processus.');
            return 1;
        }
        
        $this->info('Arrêt des processus avec les PIDs: ' . implode(', ', $pids));
        
        // Arrêter les processus
        exec('kill ' . implode(' ', $pids), $output, $returnVar);
        
        if ($returnVar === 0) {
            $this->info('Tous les workers de file d\'attente ont été arrêtés avec succès!');
            return 0;
        } else {
            $this->error('Une erreur est survenue lors de l\'arrêt des workers de file d\'attente.');
            $this->error('Sortie: ' . implode("\n", $output));
            return 1;
        }
    }
} 