<?php

namespace App\Models\Dae;

use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class DaeContrat extends DaeBaseModel
{
    use SoftDeletes;

    protected $table = 'dae_contrats';

    protected $fillable = [
        'client_id', 'reference', 'titre', 'type_contrat', 'partie_adverse',
        'date_signature', 'date_debut', 'date_fin', 'duree_mois',
        'montant', 'devise', 'objet', 'conditions', 'statut',
        'fichier', 'date_preavis', 'date_renouvellement', 'renouvelable',
        'renouvele_le', 'tags', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'date_signature' => 'date',
            'date_debut' => 'date',
            'date_fin' => 'date',
            'date_preavis' => 'date',
            'date_renouvellement' => 'date',
            'renouvele_le' => 'date',
            'renouvelable' => 'boolean',
            'montant' => 'decimal:2',
        ];
    }

    protected function getDaeModuleName(): string
    {
        return 'contrats';
    }

    public function scopeActifs($query)
    {
        return $query->where('statut', 'actif');
    }

    public function scopeExpirationProche($query, int $jours = 30)
    {
        return $query->where('statut', 'actif')
            ->whereNotNull('date_fin')
            ->whereBetween('date_fin', [today(), today()->addDays($jours)]);
    }

    public function scopeExpires($query)
    {
        return $query->where('statut', 'actif')
            ->whereNotNull('date_fin')
            ->where('date_fin', '<', today());
    }
}
