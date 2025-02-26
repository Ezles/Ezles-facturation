<?php

namespace App\Console\Commands;

use App\Models\Devis;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Console\Command;

class TestPdfCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pdf:test {quote_number} {--template=quote_simple : Template to use}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test PDF generation with a simple template';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $quoteNumber = $this->argument('quote_number');
        $template = $this->option('template');
        
        $this->info("Looking for quote with number: {$quoteNumber}");
        $quote = Devis::with(['client', 'lignes', 'user'])
            ->where('numero', $quoteNumber)
            ->first();
        
        if (!$quote) {
            $this->error("Quote not found: {$quoteNumber}");
            return 1;
        }
        
        $this->info("Quote found: {$quote->numero}");
        $this->info("Client: " . ($quote->client ? $quote->client->nom : 'No client'));
        $this->info("Amount: " . number_format($quote->total_ttc, 2, ',', ' ') . " €");
        
        // Ensure dates are properly formatted
        if ($quote->date_emission) {
            if (!$quote->date_emission instanceof \Carbon\Carbon) {
                $quote->date_emission = \Carbon\Carbon::parse($quote->date_emission);
            }
        }
        
        if ($quote->date_validite) {
            if (!$quote->date_validite instanceof \Carbon\Carbon) {
                $quote->date_validite = \Carbon\Carbon::parse($quote->date_validite);
            }
        }
        
        $this->info("Generating PDF with template: pdf.{$template}");
        
        try {
            $pdf = PDF::loadView("pdf.{$template}", [
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
            
            $outputFile = "storage/app/test_pdf_{$quote->numero}.pdf";
            $pdf->save($outputFile);
            
            $this->info("PDF generated successfully: {$outputFile}");
            return 0;
        } catch (\Exception $e) {
            $this->error("Error generating PDF: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
            return 1;
        }
    }
} 