<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Gel\GelMessage;

class MessageEnvoyeEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    /**
     * Create a new event instance.
     */
    public function __construct(GelMessage $message)
    {
        $this->message = $message;
    }

    /**
     * S11 : 3 canaux de messagerie.
     * - entreprise        → chat.{cabinet}.{client}     (Entreprise ↔ Secrétaire)
     * - interne_comptable → chat.interne.{cabinet}.{id} (Secrétaire ↔ Comptable)
     * - interne_admin     → chat.interne.{cabinet}.{id} (Secrétaire ↔ Administrateur)
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    /**
     * Nom lisible de l'événement sur le fil WebSocket.
     * Sans broadcastAs(), le fil expose le FQCN App\Events\MessageEnvoyeEvent,
     * qui ne correspond jamais au `.listen('MessageEnvoyeEvent')` des vues.
     */
    public function broadcastAs(): string
    {
        return 'MessageEnvoyeEvent';
    }

    public function broadcastOn(): array
    {
        $msg = $this->message;

        // Canal entreprise : lié au client
        if ($msg->channel === GelMessage::CHANNEL_ENTREPRISE) {
            return [
                new PrivateChannel('chat.' . $msg->cabinet_id . '.' . $msg->client_id),
            ];
        }

        // S4.1 — Canal de coordination dédié, scoped par entreprise (client_id).
        // Le secrétaire ET le comptable rattachés à cette entreprise écoutent
        // chat.coordination.{client_id} → isolation stricte par entreprise.
        if ($msg->channel === GelMessage::CHANNEL_COORDINATION) {
            return [
                new PrivateChannel('chat.coordination.' . $msg->client_id),
            ];
        }

        // Canaux internes : chaque interlocuteur écoute son propre canal user.
        $channels = [];
        // Canal du récepteur (celui qui doit voir le message arriver en direct)
        if ($msg->receiver_id) {
            $channels[] = new PrivateChannel('chat.interne.' . $msg->cabinet_id . '.' . $msg->receiver_id);
        }
        // Canal de l'émetteur (pour rafraîchir l'historique sur un autre appareil)
        $channels[] = new PrivateChannel('chat.interne.' . $msg->cabinet_id . '.' . $msg->sender_id);

        return $channels;
    }
}
