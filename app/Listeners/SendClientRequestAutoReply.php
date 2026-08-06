<?php

namespace App\Listeners;

use App\Events\ClientRequestCreated;
use App\Mail\ClientRequestReceivedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendClientRequestAutoReply implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ClientRequestCreated $event): void
    {
        Mail::to($event->clientRequest->email)
            ->send(new ClientRequestReceivedMail($event->clientRequest));
    }
}
