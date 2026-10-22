<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DeclinedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function build()
    {
        return $this->subject('Cooperative Membership Application Declined')
            ->from('your@email.com', 'Kingsland Pala-Pala Cooperative')
            ->view('emails.declined');
    }
}
