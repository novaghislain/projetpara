<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegalContract extends LegalBaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'legal_contracts';

    protected $fillable = [
        'client_id', 'reference', 'titre', 'type', 'statut',
        'parties', 'objet',
        'date_signature', 'date_debut', 'date_fin',
        'renouvellement_auto', 'alerte_avant',
        'montant', 'devise', 'modalites_paiement',
        'clauses_specifiques', 'penalites',
        'tribunal_competent', 'droit_applicable',
        'document_path', 'version', 'historique_versions',
        'responsable', 'created_by',
    ];

    protected $casts = [
        'parties' => 'json',
        'clauses_specifiques' => 'json',
        'historique_versions' => 'json',
        'date_signature' => 'date',
        'date_debut' => 'date',
        'date_fin' => 'date',
        'montant' => 'decimal:2',
        'renouvellement_auto' => 'boolean',
    ];

    public function scopeActifs($query)
    {
        return $query->whereIn('statut', ['signé', 'actif']);
    }

    public function scopeExpireBientot($query, int $jours = 30)
    {
        return $query->whereIn('statut', ['signé', 'actif'])
            ->whereNotNull('date_fin')
            ->whereBetween('date_fin', [now(), now()->addDays($jours)]);
    }

    public function signatures()
    {
        return $this->hasMany(LegalContractSignature::class, 'contract_id');
    }
}
