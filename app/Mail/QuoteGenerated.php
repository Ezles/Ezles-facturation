<?php

namespace App\Mail;

use App\Models\Devis;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;
use Barryvdh\DomPDF\Facade\Pdf;

class QuoteGenerated extends Mailable
{
    use SerializesModels;

    /**
     * Le devis à envoyer.
     */
    public $devis;

    /**
     * Create a new message instance.
     */
    public function __construct(Devis $devis)
    {
        $this->devis = $devis;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Devis ' . $this->devis->numero . ' - Ezles',
            from: config('mail.from.address', 'contact@ezles.dev'),
            replyTo: [
                new \Illuminate\Mail\Mailables\Address(config('mail.from.address', 'contact@ezles.dev'), config('mail.from.name', 'Ezles')),
            ],
            tags: ['devis', 'client'],
            metadata: [
                'devis_id' => $this->devis->id,
                'client_id' => $this->devis->client_id ?? null,
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.quote-new',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        try {
            // Ensure dates are properly formatted
            if ($this->devis->date_emission) {
                if (!$this->devis->date_emission instanceof \Carbon\Carbon) {
                    $this->devis->date_emission = \Carbon\Carbon::parse($this->devis->date_emission);
                }
            }
            
            if ($this->devis->date_validite) {
                if (!$this->devis->date_validite instanceof \Carbon\Carbon) {
                    $this->devis->date_validite = \Carbon\Carbon::parse($this->devis->date_validite);
                }
            }
            
            // Générer le PDF du devis
            $pdf = PDF::loadView('pdf.quote_fixed', [
                'devis' => $this->devis
            ]);

            // Optimiser le PDF pour qu'il tienne sur une seule page
            $pdf->setPaper('a4', 'portrait');
            $pdf->setOptions([
                'dpi' => 150,
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'isFontSubsettingEnabled' => true,
                'isPhpEnabled' => true,
            ]);

            // Format professionnel pour le nom du fichier
            $fileName = 'Devis_' . $this->devis->numero . '_' . 
                ($this->devis->date_emission ? $this->devis->date_emission->format('Y-m-d') : date('Y-m-d')) . '.pdf';

            return [
                Attachment::fromData(fn () => $pdf->output(), $fileName)
                    ->withMime('application/pdf'),
            ];
        } catch (\Exception $e) {
            // Log l'erreur mais continue l'envoi de l'email sans pièce jointe
            \Log::error('Erreur lors de la génération du PDF pour l\'email', [
                'devis_id' => $this->devis->id,
                'error' => $e->getMessage()
            ]);
            
            return [];
        }
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->withSymfonyMessage(function ($message) {
            $message->getHeaders()
                ->addTextHeader('X-Priority', '1')
                ->addTextHeader('X-MSMail-Priority', 'High')
                ->addTextHeader('Importance', 'High')
                ->addTextHeader('X-Mailer', 'Ezles Facturation');
        });
    }
}
