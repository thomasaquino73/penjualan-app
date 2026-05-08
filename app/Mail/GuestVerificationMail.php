<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class GuestVerificationMail extends Mailable
{
    public $user;

    public $url;

    public function __construct($user, $url)
    {
        $this->user = $user;
        $this->url = $url;
    }

    public function build()
    {
        return $this->subject('Verifikasi Email Anda')
            ->view('emails.verify-email')
            ->with([
                'name' => $this->user->fullname,
                'url' => $this->url,
            ]);
    }
}
