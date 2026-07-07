<?php

namespace App\Models\Dae;

use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class DaeMeetingMinute extends DaeBaseModel
{
    use SoftDeletes;

    protected $table = 'dae_meeting_minutes';

    protected $fillable = [
        'client_id', 'titre', 'objet', 'lieu', 'date_reunion',
        'heure_debut', 'heure_fin', 'participants', 'ordre_du_jour',
        'discussion', 'decisions', 'prochaine_reunion',
        'statut', 'redige_par', 'approuve_par', 'approuve_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date_reunion' => 'date',
            'prochaine_reunion' => 'date',
            'participants' => 'array',
            'ordre_du_jour' => 'array',
            'discussion' => 'array',
            'decisions' => 'array',
            'approuve_at' => 'datetime',
        ];
    }

    protected function getDaeModuleName(): string
    {
        return 'pv_reunions';
    }

    // ─── Relations ────────────────────────────────────────

    public function redacteur()
    {
        return $this->belongsTo(User::class, 'redige_par');
    }

    public function approbateur()
    {
        return $this->belongsTo(User::class, 'approuve_par');
    }

    // ─── Scopes ───────────────────────────────────────────

    public function scopeRecents($query)
    {
        return $query->orderBy('date_reunion', 'desc');
    }

    public function scopeByStatut($query, string $statut)
    {
        return $query->where('statut', $statut);
    }
}
