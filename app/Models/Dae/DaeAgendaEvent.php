<?php

namespace App\Models\Dae;

use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle représentant un événement d'agenda dans le module DAE.
 *
 * Table associée : `dae_agenda_events`
 *
 * Un événement peut être une réunion, un rendez-vous, un rappel, etc.
 * Il peut être ponctuel ou récurrent, avec des participants et des rappels.
 */
class DaeAgendaEvent extends DaeBaseModel
{
    use SoftDeletes;

    protected $table = 'dae_agenda_events';

    protected $fillable = [
        'client_id', 'title', 'description', 'type',
        'start_at', 'end_at', 'all_day', 'location', 'couleur',
        'statut', 'rappel', 'participants', 'recurrence',
        'recurrence_end', 'created_by',
        // Phase 2: Visio & Invitations
        'visio_type', 'visio_link', 'invitation_sent', 'guest_email', 'guest_name',
    ];

    protected function casts(): array
    {
        return [
            'all_day' => 'boolean',
            'invitation_sent' => 'boolean',
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'rappel' => 'array',
            'participants' => 'array',
            'recurrence_end' => 'date',
        ];
    }

    protected function getDaeModuleName(): string
    {
        return 'agenda';
    }

    public function scopeByPeriode($query, $start, $end)
    {
        return $query->whereBetween('start_at', [$start, $end]);
    }

    public function scopeDuJour($query)
    {
        return $query->whereDate('start_at', today());
    }

    public function scopeAVenir($query)
    {
        return $query->where('start_at', '>=', now())
            ->where('statut', '!=', 'annule');
    }
}
