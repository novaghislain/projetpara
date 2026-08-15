<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Dae\DaeAgendaEvent;
use Illuminate\Support\Facades\Mail;
use App\Mail\AgendaReminderMail;
use Carbon\Carbon;

class SendAgendaReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'agenda:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send automatic email reminders for tomorrow\'s agenda events';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tomorrow = Carbon::tomorrow()->toDateString();

        $events = DaeAgendaEvent::whereDate('start_at', $tomorrow)
            ->whereNotNull('guest_email')
            ->where('statut', 'planifie')
            ->get();

        $count = 0;
        foreach ($events as $event) {
            try {
                $cabinetName = config('app.name', 'Cabinet');
                Mail::to($event->guest_email)->send(new AgendaReminderMail($event, $cabinetName));
                $count++;
            } catch (\Exception $e) {
                $this->error('Failed to send reminder for event ' . $event->id . ': ' . $e->getMessage());
            }
        }

        $this->info("Successfully sent {$count} agenda reminders for tomorrow.");
    }
}
