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
 * The club's standard welcome, sent automatically to anyone who uses the
 * contact form. It answers the questions a newcomer always asks — when, where,
 * how much, and who do I speak to — so that nobody is left waiting on a
 * volunteer to find a spare evening.
 *
 * Replies are directed to the first configured enquiry recipient, so that a
 * newcomer answering this email reaches a person rather than an unattended
 * mailbox. All configured administrators still receive the original enquiry.
 */
class EnquiryAcknowledgement extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Enquiry $enquiry) {}

    public function envelope(): Envelope
    {
        $envelope = new Envelope(
            subject: config('club.auto_reply.subject'),
        );

        if ($replyTo = config('club.enquiry_emails.0')) {
            $envelope->replyTo = [new Address($replyTo, config('club.name'))];
        }

        return $envelope;
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.enquiry-acknowledgement',
            with: [
                'enquiry' => $this->enquiry,
                'venue' => config('club.venue'),
                'meeting' => config('club.meeting'),
                'juniorsVenue' => config('club.juniors_venue'),
                'officers' => config('club.officers'),
                'teams' => config('club.teams'),
                'coaching' => config('club.coaching'),
                'links' => config('club.links'),
                'signature' => config('club.auto_reply.signature'),
                'signatureRole' => config('club.auto_reply.signature_role'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
