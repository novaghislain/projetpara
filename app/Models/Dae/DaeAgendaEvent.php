<?php

namespace App\Models\Dae;

use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class DaeAgendaEvent extends DaeBaseModel
{
    use SoftDeletes;

    protected $table = 'dae_agenda_events';

    protected $fillable = [
        'client_id', 'title', 'description', 'type',
        'start_at', 'end_at', 'all_day', 'location', 'couleur',
        'statut', 'rappel', 'participants', 'recurrence',
        'recurrence_end', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'all_day' => 'boolean',
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
