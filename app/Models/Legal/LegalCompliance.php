<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegalCompliance extends LegalBaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'legal_compliance';

    protected $fillable = [
        'client_id',
        'intitule', 'type', 'organisme', 'periodicite',
        'date_echeance', 'date_derniere_conformite',
        'statut', 'alerte_avant', 'document_path',
        'notes', 'responsable', 'created_by',
    ];

    protected $casts = [
        'date_echeance' => 'date',
        'date_derniere_conformite' => 'date',
    ];

    public function scopeEcheantes($query)
    {
        return $query->where('date_echeance', '<=', now()->addDays(30))
            ->whereIn('statut', ['non_conforme', 'à_vérifier', 'expiré']);
    }
}
