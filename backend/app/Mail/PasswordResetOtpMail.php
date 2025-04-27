<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;
    public $type;

    public function __construct($otp, $type)
    {
        $this->otp = $otp;
        $this->type = $type;
    }

    public function build()
    {
        if ($this->type === 'reset') {
            return $this->subject('Il tuo codice OTP per il reset della password')
            ->view('emails.password-reset-otp');
            
        } elseif ($this->type === 'verify') {
            return $this->subject('Verifica il tuo indirizzo email')
            ->view('emails.registration-otp');
        }

       
    }
}