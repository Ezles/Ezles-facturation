<?php

namespace App\Console\Commands;

use App\Models\Facture;
use Illuminate\Console\Command;

class UpdateInvoiceStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoice:update-status {invoice_number} {status}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the status of an invoice';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $invoiceNumber = $this->argument('invoice_number');
        $status = $this->argument('status');
        
        $facture = Facture::where('numero', $invoiceNumber)->first();
        
        if (!$facture) {
            $this->error("Invoice with number {$invoiceNumber} not found.");
            return 1;
        }
        
        $oldStatus = $facture->statut;
        $facture->statut = $status;
        $facture->save();
        
        $this->info("Invoice status updated from '{$oldStatus}' to '{$status}'.");
        
        return 0;
    }
} 