<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ContactMessage $messageRecord)
    {
    }

    public function build(): self
    {
        return $this->subject('Nueva consulta desde fitinox.com.ar')
            ->view('emails.contact-message');
    }
}
