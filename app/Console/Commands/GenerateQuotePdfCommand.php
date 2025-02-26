<?php

namespace App\Console\Commands;

use App\Models\Devis;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Console\Command;

class GenerateQuotePdfCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quote:pdf {quote_number} {--output=quote.pdf : Output file name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a PDF for a specific quote';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $quoteNumber = $this->argument('quote_number');
        $outputFile = $this->option('output');
        
        // Find the quote
        $this->info("Looking for quote with number: {$quoteNumber}");
        $quote = Devis::with(['client', 'lignes'])
            ->where('numero', $quoteNumber)
            ->first();
        
        if (!$quote) {
            $this->error("Quote not found: {$quoteNumber}");
            return 1;
        }
        
        $this->info("Quote found: {$quote->numero}");
        $this->info("Client: {$quote->client->nom}");
        $this->info("Amount: " . number_format($quote->total_ttc, 2, ',', ' ') . " €");
        
        // Generate PDF
        $this->info("Generating PDF...");
        
        try {
            $pdf = PDF::loadView('pdf.quote', [
                'devis' => $quote
            ]);
            
            $pdf->setPaper('a4', 'portrait');
            $pdf->setOptions([
                'dpi' => 150,
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'isFontSubsettingEnabled' => true,
                'isPhpEnabled' => true,
            ]);
            
            $pdf->save($outputFile);
            
            $this->info("PDF generated successfully: {$outputFile}");
            return 0;
        } catch (\Exception $e) {
            $this->error("Error generating PDF: " . $e->getMessage());
            return 1;
        }
    }
} 