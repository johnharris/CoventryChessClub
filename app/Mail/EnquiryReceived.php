<?php

namespace App\Mail;

use App\Models\Enquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Notifies the club when someone uses the contact form. Sent only when
 * CLUB_ENQUIRY_EMAIL is set; the enquiry is always stored in the site inbox.
 */
class EnquiryReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Enquiry $enquiry) {}

    /**
     * Replies go straight back to the person who filled in the form.
     */
    public function envelope(): Envelope
    {
        $subject = $this->enquiry->subject
            ?: $this->enquiry->typeLabel().' from '.$this->enquiry->name;

        return new Envelope(
            subject: '['.config('club.name').'] '.$subject,
            replyTo: [new Address($this->enquiry->email, $this->enquiry->name)],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.enquiry-received',
            with: ['enquiry' => $this->enquiry],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
