<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegalLitigation extends LegalBaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'legal_litigations';

    protected $fillable = [
        'client_id', 'reference', 'titre', 'type', 'nature',
        'partie_adverse', 'partie_adverse_avocat',
        'statut', 'tribunal', 'numero_dossier',
        'date_saisine', 'prochaine_audience',
        'avocat_cabinet',
        'montant_litige', 'montant_risque', 'provisions_constituees',
        'documents', 'historique',
        'assigned_to', 'created_by',
    ];

    protected $casts = [
        'documents' => 'json',
        'historique' => 'json',
        'date_saisine' => 'date',
        'prochaine_audience' => 'date',
        'montant_litige' => 'decimal:2',
        'montant_risque' => 'decimal:2',
        'provisions_constituees' => 'decimal:2',
    ];

    public function scopeEnCours($query)
    {
        return $query->whereNotIn('statut', [
            'clôturé_gagné', 'clôturé_perdu', 'clôturé_transaction'
        ]);
    }
}
