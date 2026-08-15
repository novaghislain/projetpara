<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Dae\DaeAgendaEvent;

class AgendaReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $event;
    public $cabinetName;

    /**
     * Create a new message instance.
     */
    public function __construct(DaeAgendaEvent $event, $cabinetName)
    {
        $this->event = $event;
        $this->cabinetName = $cabinetName;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Rappel : Rendez-vous demain - ' . $this->cabinetName)
                    ->view('emails.agenda.reminder');
    }
}
