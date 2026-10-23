<?php

namespace App\Mail;

use App\Models\Agency;
use App\Models\Booking;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContractMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking, public Agency $agency) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Votre contrat de location — ' . $this->booking->reference);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.contract');
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(
                fn () => Pdf::loadView('pdf.contract', [
                    'booking' => $this->booking->loadMissing(['customer', 'vehicle.category']),
                    'agency' => $this->agency,
                ])->setPaper('a4')->output(),
                'contrat-' . $this->booking->reference . '.pdf'
            )->withMime('application/pdf'),
        ];
    }
}
