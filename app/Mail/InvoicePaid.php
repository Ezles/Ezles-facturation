<?php

namespace App\Mail;

use App\Models\Facture;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoicePaid extends Mailable
{
    use SerializesModels;

    /**
     * La facture à envoyer.
     */
    public $facture;

    /**
     * Create a new message instance.
     */
    public function __construct(Facture $facture)
    {
        $this->facture = $facture;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Paiement reçu pour la facture ' . $this->facture->numero . ' - Ezles',
            from: config('mail.from.address', 'contact@ezles.dev'),
            replyTo: [
                new \Illuminate\Mail\Mailables\Address(config('mail.from.address', 'contact@ezles.dev'), config('mail.from.name', 'Ezles')),
            ],
            tags: ['facture', 'paiement', 'client'],
            metadata: [
                'facture_id' => $this->facture->id,
                'client_id' => $this->facture->client_id,
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice-paid',
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
            // Générer le PDF de la facture
            $pdf = PDF::loadView('pdf.receipt', [
                'facture' => $this->facture
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
            $fileName = 'Recu_Paiement_' . $this->facture->numero . '_' . $this->facture->date_emission->format('Y-m-d') . '.pdf';

            return [
                Attachment::fromData(fn () => $pdf->output(), $fileName)
                    ->withMime('application/pdf'),
            ];
        } catch (\Exception $e) {
            // Log l'erreur mais continue l'envoi de l'email sans pièce jointe
            \Log::error('Erreur lors de la génération du PDF pour l\'email', [
                'facture_id' => $this->facture->id,
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