<?php

namespace App\Models\Dae;

use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class DaeConformite extends DaeBaseModel
{
    use SoftDeletes;

    protected $table = 'dae_conformite';

    protected $fillable = [
        'client_id', 'type_conformite', 'titre', 'description',
        'exigence_reglementaire', 'autorite_competente',
        'date_soumission', 'date_validation', 'date_expiration',
        'statut', 'pieces_jointes', 'notes',
        'created_by', 'verified_by', 'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'pieces_jointes' => 'array',
            'date_soumission' => 'date',
            'date_validation' => 'date',
            'date_expiration' => 'date',
            'verified_at' => 'datetime',
        ];
    }

    protected function getDaeModuleName(): string
    {
        return 'conformite';
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function scopeNonConforme($query)
    {
        return $query->whereIn('statut', ['non_conforme', 'expire']);
    }
}
