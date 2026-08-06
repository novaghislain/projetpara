<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * S4.3 — Diffusion temps réel d'une nouvelle activité de coordination sur une
 * entreprise. Le fil partagé (S1.2/S4.3) se rafraîchit sans rechargement.
 * Écoute sur chat.coordination.{client_id} (identité portée par l'event).
 */
class CoordinationActivityEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $clientId;
    public $activity;

    public function __construct(int $clientId, array $activity)
    {
        $this->clientId = $clientId;
        $this->activity = $activity;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.coordination.' . $this->clientId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'CoordinationActivityEvent';
    }
}