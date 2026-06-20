<?php

namespace App\Models\Dae;

use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class DaePersonnelDossier extends DaeBaseModel
{
    use SoftDeletes;

    protected $table = 'dae_personnel_dossiers';

    protected $fillable = [
        'client_id', 'nom', 'prenom', 'email', 'telephone',
        'poste', 'departement', 'date_embauche', 'date_depart',
        'statut', 'type_contrat', 'salaire', 'numero_securite_sociale',
        'documents', 'competences', 'informations_bancaires',
        'urgences', 'notes', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'documents' => 'array',
            'competences' => 'array',
            'informations_bancaires' => 'array',
            'urgences' => 'array',
            'date_embauche' => 'date',
            'date_depart' => 'date',
            'salaire' => 'decimal:2',
        ];
    }

    protected function getDaeModuleName(): string
    {
        return 'personnel';
    }

    public function scopeActifs($query)
    {
        return $query->where('statut', 'actif');
    }

    public function scopeByDepartement($query, string $departement)
    {
        return $query->where('departement', $departement);
    }

    public function getNomCompletAttribute(): string
    {
        return trim($this->prenom . ' ' . $this->nom);
    }
}
