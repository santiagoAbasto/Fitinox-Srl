<?php

namespace App\Mail;

use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewsletterMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public NewsletterCampaign $campaign, public NewsletterSubscriber $subscriber)
    {
    }

    public function build(): self
    {
        $mail = $this->subject($this->campaign->subject)
            ->view('emails.newsletter');

        if ($this->campaign->attachment_path && \Storage::disk('public')->exists($this->campaign->attachment_path)) {
            $mail->attachFromStorageDisk('public', $this->campaign->attachment_path);
        }

        return $mail;
    }
}
