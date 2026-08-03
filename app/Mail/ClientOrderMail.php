<?php

namespace App\Mail;

use App\Models\ClientOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClientOrderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ClientOrder $order, public string $recipientType = 'client')
    {
    }

    public function build(): self
    {
        return $this->subject("Pedido {$this->order->number} | Fitinox")
            ->view('emails.client-order');
    }
}
