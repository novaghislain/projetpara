<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Événement de coordination Secrétaire ↔ Comptable sur une entreprise (S1.2/S1.4/S4.3).
 *
 * Chaque interaction entre les deux professionnels d'une même entreprise est
 * journalisée ici (en français) et alimente le fil d'activité commun ainsi que
 * l'Historique consultable par l'Administrateur d'Entreprise (lecture seule).
 * Table : coordination_events.
 */
class CoordinationEvent extends Model
{
    protected $table = 'coordination_events';

    protected $fillable = [
        'client_id',
        'cabinet_id',
        'actor_id',
        'actor_role',
        'actor_name',
        'recipient_id',
        'type',
        'document_id',
        'task_id',
        'message_id',
        'subject',
        'body',
        'link',
        'icon',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // Types d'événements de coordination (journalisés en français via subject/body).
    public const TYPE_DOCUMENT_TRANSMIS         = 'document_transmis';
    public const TYPE_DOCUMENT_DEMANDE          = 'document_demande';
    public const TYPE_NOTE_LIEE                 = 'note_liee';
    public const TYPE_CONFIRMATION_TRAITEMENT   = 'confirmation_traitement';
    public const TYPE_ALERTE                    = 'alerte';
    public const TYPE_MESSAGE                   = 'message_coordination';
    public const TYPE_DEMANDES_INFOS            = 'demandes_infos';

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'actor_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'recipient_id');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Document::class, 'document_id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }
}
