<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle LegalCompliance (Conformité légale/réglementaire).
 *
 * Gère les obligations de conformité légale et réglementaire :
 * échéances, organismes de contrôle, périodicité, et suivi
 * du statut de conformité avec alertes automatiques.
 * Table associée : `legal_compliance`.
 *
 * @property int $id
 * @property int $client_id ID du client
 * @property string $intitule Intitulé de l'obligation
 * @property string $type Type (fiscal, social, juridique, environnemental)
 * @property string|null $organisme Organisme de contrôle
 * @property string|null $periodicité Périodicité (mensuelle, trimestrielle, annuelle)
 * @property \Carbon\Carbon|null $date_echeance Prochaine échéance
 * @property \Carbon\Carbon|null $date_derniere_conformite Dernière mise en conformité
 * @property string $statut Statut (conforme, non_conforme, à_vérifier, expiré)
 * @property int $alerte_avant Nombre de jours d'alerte avant échéance
 * @property string|null $document_path Chemin du document
 * @property string|null $notes Notes
 * @property string|null $responsable Responsable du suivi
 * @property int $created_by ID du créateur
 */
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
