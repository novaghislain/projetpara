<?php

namespace App\Models\Dae;

use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class DaeTache extends DaeBaseModel
{
    use SoftDeletes;

    protected $table = 'dae_taches';

    protected $fillable = [
        'client_id', 'titre', 'description', 'priorite', 'statut',
        'echeance', 'assigned_to', 'completed_at', 'parent_id',
        'tags', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'echeance' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    protected function getDaeModuleName(): string
    {
        return 'taches';
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function parent()
    {
        return $this->belongsTo(DaeTache::class, 'parent_id');
    }

    public function sousTaches()
    {
        return $this->hasMany(DaeTache::class, 'parent_id');
    }

    public function scopeByPriorite($query, string $priorite)
    {
        return $query->where('priorite', $priorite);
    }

    public function scopeEnCours($query)
    {
        return $query->whereIn('statut', ['a_faire', 'en_cours']);
    }

    public function scopeEnRetard($query)
    {
        return $query->whereNotNull('echeance')
            ->where('echeance', '<', today())
            ->whereNotIn('statut', ['terminee', 'annulee']);
    }
}
