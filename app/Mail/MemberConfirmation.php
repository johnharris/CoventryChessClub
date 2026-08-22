<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use App\Models\User;
use App\Support\EmailTemplateVariables;
use App\Support\Markdown;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MemberConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    protected ?EmailTemplate $resolvedTemplate = null;

    /** @param array<string, mixed>|null $templateData */
    public function __construct(
        public User $member,
        public ?array $templateData = null,
        public bool $isTest = false,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->template()->renderSubject($this->variables());

        return new Envelope(
            subject: $this->isTest ? '[Test] '.$subject : $subject,
        );
    }

    public function content(): Content
    {
        $template = $this->template();

        return new Content(
            markdown: 'mail.configurable',
            with: [
                'bodyHtml' => Markdown::toHtml($template->renderBody($this->variables())),
                'signature' => $template->signature,
                'signatureRole' => $template->signature_role,
                'isTest' => $this->isTest,
                'footer' => 'This message confirms that a member account was created on the Coventry Chess Club website.',
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }

    protected function template(): EmailTemplate
    {
        return $this->resolvedTemplate ??= $this->templateData
            ? new EmailTemplate([
                'key' => EmailTemplate::MEMBER_CONFIRMATION,
                ...$this->templateData,
            ])
            : EmailTemplate::current(EmailTemplate::MEMBER_CONFIRMATION);
    }

    /** @return array<string, string> */
    protected function variables(): array
    {
        return EmailTemplateVariables::forMember($this->member);
    }
}
