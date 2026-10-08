<?php

namespace App\Mail;

use App\Models\EmailCampaign;
use App\Models\EmailRecipient;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class CampaignMail extends Mailable
{
    private string $subjectLine;
    private string $htmlBody;

    public function __construct(private EmailCampaign $campaign, EmailRecipient $recipient)
    {
        [$this->subjectLine, $this->htmlBody] = $campaign->renderFor($recipient->name, $recipient->email);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.campaign', with: ['htmlBody' => $this->htmlBody]);
    }

    public function attachments(): array
    {
        if (! $this->campaign->attachment_path) {
            return [];
        }

        return [
            Attachment::fromStorageDisk('local', $this->campaign->attachment_path)
                ->as($this->campaign->attachment_name),
        ];
    }
}
