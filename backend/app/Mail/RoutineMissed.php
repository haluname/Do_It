<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RoutineMissed extends Mailable
{
    use Queueable, SerializesModels;

    public $routine;

    public function __construct($routine)
    {
        $this->routine = $routine;
    }

    public function build()
    {
        $frequencyLabels = [
            'daily' => 'giornaliera',
            'every_2_days' => 'ogni 2 giorni',
            'weekly' => 'settimanale',
            'monthly' => 'mensile'
        ];
        
        $frequency = $frequencyLabels[$this->routine->frequency] ?? $this->routine->frequency;
        
        return $this->subject('Hai saltato una routine: ' . $this->routine->title)
                    ->markdown('emails.routine_missed')
                    ->with([
                        'routine' => $this->routine,
                        'frequency' => $frequency
                    ]);
    }
}