<?php

namespace App\Mail;

use App\Models\Facture;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceThanks extends Mailable
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
            subject: 'Merci pour votre confiance - Ezles',
            from: config('mail.from.address', 'contact@ezles.dev'),
            replyTo: [
                new \Illuminate\Mail\Mailables\Address(config('mail.from.address', 'contact@ezles.dev'), config('mail.from.name', 'Ezles')),
            ],
            tags: ['facture', 'remerciement', 'client'],
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
            view: 'emails.invoice-thanks',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        // Pas de pièce jointe pour l'email de remerciement
        return [];
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