<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClientCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $client, public ?string $temporaryPassword = null)
    {
    }

    public function build(): self
    {
        return $this->subject('Acceso a Zona Clientes | Fitinox')
            ->view('emails.client-credentials');
    }
}
