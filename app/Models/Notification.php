<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle Notification (Notification utilisateur).
 *
 * Gère les notifications destinées aux utilisateurs de la plateforme.
 * Chaque notification a un type, un titre, un message textuel,
 * et des données additionnelles (JSON) pour les actions contextuelles.
 * Supporte le marquage comme lue (read_at).
 *
 * @property int $id
 * @property int $user_id ID du destinataire
 * @property string $type Type de notification (info, warning, success, error)
 * @property string $title Titre de la notification
 * @property string $message Corps du message
 * @property array|null $data Données additionnelles (JSON)
 * @property \Carbon\Carbon|null $read_at Date de lecture
 *
 * @property-read \App\Models\User $user Utilisateur destinataire
 */
class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'read_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function markAsRead(): void
    {
        $this->update(['read_at' => now()]);
    }
}
