<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CompanyLoginMail extends Mailable
{
    use Queueable, SerializesModels;

    public $email;
    public $password;

    public function __construct($email, $password)
    {
        $this->email = $email;
        $this->password = $password;
    }

    public function build()
    {
        return $this->subject('Welcome - Your Company Login Details')
            ->view('emails.company_login')
            ->with([
                'email'    => $this->email,
                'password' => $this->password,
                'url'      => route('login'),
            ]);
    }
}