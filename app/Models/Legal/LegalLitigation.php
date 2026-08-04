<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle LegalLitigation (Litige / Contentieux).
 *
 * Gère les litiges et contentieux en cours avec la partie adverse,
 * le tribunal saisi, les montants en jeu et les provisions constituées.
 * Table associée : `legal_litigations`.
 *
 * @property int $id
 * @property int $client_id ID du client
 * @property string $reference Référence du litige
 * @property string $titre Titre du litige
 * @property string $type Type (commercial, social, fiscal, civil, etc.)
 * @property string|null $nature Nature du litige
 * @property string|null $partie_adverse Nom de la partie adverse
 * @property string|null $partie_adverse_avocat Avocat de la partie adverse
 * @property string $statut Statut (en_cours, clôturé_gagné, clôturé_perdu, clôturé_transaction)
 * @property string|null $tribunal Tribunal saisi
 * @property string|null $numero_dossier Numéro de dossier au tribunal
 * @property \Carbon\Carbon|null $date_saisine Date de saisine
 * @property \Carbon\Carbon|null $prochaine_audience Prochaine audience
 * @property string|null $avocat_cabinet Cabinet d'avocat
 * @property float|null $montant_litige Montant du litige
 * @property float|null $montant_risque Montant du risque estimé
 * @property float|null $provisions_constituees Provisions constituées
 * @property array|null $documents Documents (JSON)
 * @property array|null $historique Historique du dossier (JSON)
 * @property int|null $assigned_to ID de l'assigné
 * @property int $created_by ID du créateur
 */
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
