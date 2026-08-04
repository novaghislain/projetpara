<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle LegalContract (Contrat juridique).
 *
 * Gère les contrats avec leurs parties, clauses, montants,
 * périodes de validité et historique des versions.
 * Supporte le renouvellement automatique et les alertes d'échéance.
 * Table associée : `legal_contracts`.
 *
 * @property int $id
 * @property int $client_id ID du client
 * @property string $reference Référence du contrat
 * @property string $titre Titre du contrat
 * @property string $type Type (bail, prestation, vente, travail, etc.)
 * @property string $statut Statut (brouillon, signé, actif, expiré, résilié)
 * @property array $parties Parties contractantes (JSON)
 * @property string|null $objet Objet du contrat
 * @property \Carbon\Carbon|null $date_signature Date de signature
 * @property \Carbon\Carbon|null $date_debut Date de début d'effet
 * @property \Carbon\Carbon|null $date_fin Date de fin d'effet
 * @property bool $renouvellement_auto Renouvellement automatique
 * @property int $alerte_avant Alerte avant échéance (jours)
 * @property float|null $montant Montant du contrat
 * @property string|null $devise Devise
 * @property string|null $modalites_paiement Modalités de paiement
 * @property array|null $clauses_specifiques Clauses spécifiques (JSON)
 * @property string|null $penalites Pénalités prévues
 * @property string|null $tribunal_competent Tribunal compétent
 * @property string|null $droit_applicable Droit applicable
 * @property string|null $document_path Chemin du document
 * @property string $version Version du contrat
 * @property array|null $historique_versions Historique des versions (JSON)
 * @property string|null $responsable Responsable du suivi
 * @property int $created_by ID du créateur
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Legal\LegalContractSignature[] $signatures Signatures du contrat
 */
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
