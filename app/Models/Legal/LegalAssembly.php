<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class LegalAssembly extends LegalBaseModel
{
    use HasFactory;

    protected $table = 'legal_assemblies';

    protected $fillable = [
        'client_id', 'type', 'annee',
        'date_convocation', 'date_tenue', 'lieu',
        'quorum_requis', 'quorum_atteint',
        'ordre_du_jour', 'resolutions', 'participants',
        'statut', 'pv_path', 'pv_approuve', 'convocation_envoyee',
        'created_by',
    ];

    protected $casts = [
        'ordre_du_jour' => 'json',
        'resolutions' => 'json',
        'participants' => 'json',
        'date_convocation' => 'date',
        'date_tenue' => 'date',
        'pv_approuve' => 'boolean',
        'convocation_envoyee' => 'boolean',
        'quorum_requis' => 'decimal:2',
        'quorum_atteint' => 'decimal:2',
    ];

    public function scopePlanifiees($query)
    {
        return $query->where('statut', 'planifiée');
    }

    public function scopeTenues($query)
    {
        return $query->where('statut', 'tenue');
    }
}
