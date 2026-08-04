<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle représentant une déclaration fiscale.
 *
 * Gère les déclarations de différents types d'impôts et taxes :
 * TVA, Impôt sur les Sociétés (IS), ITS, CNSS, VPS, AIB, etc.
 * Chaque déclaration est liée à un client (entreprise) et à un
 * exercice fiscal, avec un suivi des périodes (mensuelle, trimestrielle).
 * Inclut le calcul des bases imposables, des montants dus et payés,
 * des pénalités et des acomptes.
 *
 * @property int $id
 * @property int|null $client_id Identifiant du client (entreprise)
 * @property int|null $fiscal_year_id Identifiant de l'exercice fiscal
 * @property string $tax_type Type de taxe (tva, is, its, cnss, vps, aib)
 * @property string $reference Référence de la déclaration
 * @property string $period_type Type de période (mensuelle, trimestrielle, annuelle)
 * @property int|null $period_month Mois concerné
 * @property int|null $period_quarter Trimestre concerné
 * @property int $period_year Année concernée
 * @property string|null $date_debut Date de début de période
 * @property string|null $date_fin Date de fin de période
 * @property string|null $date_echeance Date d'échéance
 * @property string|null $date_depot Date de dépôt
 * @property float|null $base_imposable Base imposable
 * @property float|null $taux Taux d'imposition
 * @property float $montant_dut Montant dû
 * @property float $montant_paye Montant payé
 * @property float $penalites Pénalités de retard
 * @property float $solde Solde restant
 * @property float|null $tva_collectee TVA collectée
 * @property float|null $tva_recuperable TVA récupérable
 * @property float|null $tva_net TVA nette à payer
 * @property float|null $credit_tva Crédit de TVA (si tva_net < 0)
 * @property float|null $resultat_fiscal Résultat fiscal
 * @property float|null $acomptes_verses Acomptes déjà versés
 * @property array|null $tranches Tranches d'imposition (JSON)
 * @property float|null $part_employeur Part employeur (CNSS)
 * @property float|null $part_salarie Part salarié (CNSS)
 * @property string $status Statut (brouillon, calcule, depose, paye)
 * @property string|null $notes Notes
 * @property int|null $created_by Identifiant du créateur
 * @property int|null $validated_by Identifiant du validateur
 * @property string|null $validated_at Date de validation
 * @property int|null $journal_id Identifiant du journal comptable associé
 *
 * @property-read Client|null $client Client (entreprise) associé
 * @property-read FiscalYear|null $fiscalYear Exercice fiscal associé
 * @property-read AccountingJournal|null $journal Journal comptable associé
 * @property-read User|null $createdBy Utilisateur créateur
 * @property-read User|null $validatedBy Utilisateur validateur
 *
 * @table accounting_tax_declarations
 */
class AccountingTaxDeclaration extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id', 'fiscal_year_id', 'tax_type', 'reference',
        'period_type', 'period_month', 'period_quarter', 'period_year',
        'date_debut', 'date_fin', 'date_echeance', 'date_depot',
        'base_imposable', 'taux', 'montant_dut', 'montant_paye',
        'penalites', 'solde',
        'tva_collectee', 'tva_recuperable', 'tva_net', 'credit_tva',
        'resultat_fiscal', 'acomptes_verses',
        'tranches',
        'part_employeur', 'part_salarie',
        'status', 'notes',
        'created_by', 'validated_by', 'validated_at',
        'journal_id',
    ];

    protected function casts(): array
    {
        return [
            'base_imposable' => 'decimal:2',
            'taux' => 'decimal:2',
            'montant_dut' => 'decimal:2',
            'montant_paye' => 'decimal:2',
            'penalites' => 'decimal:2',
            'solde' => 'decimal:2',
            'tva_collectee' => 'decimal:2',
            'tva_recuperable' => 'decimal:2',
            'tva_net' => 'decimal:2',
            'credit_tva' => 'decimal:2',
            'resultat_fiscal' => 'decimal:2',
            'acomptes_verses' => 'decimal:2',
            'part_employeur' => 'decimal:2',
            'part_salarie' => 'decimal:2',
            'tranches' => 'json',
            'date_debut' => 'date',
            'date_fin' => 'date',
            'date_echeance' => 'date',
            'date_depot' => 'date',
            'validated_at' => 'datetime',
        ];
    }

    // Relations
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(AccountingJournal::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    // Scopes
    public function scopeByTaxType($query, $type)
    {
        return $query->where('tax_type', $type);
    }

    public function scopeDepose($query)
    {
        return $query->where('status', 'depose');
    }

    public function scopeEnAttente($query)
    {
        return $query->whereIn('status', ['brouillon', 'calcule']);
    }

    public function scopeByPeriod($query, $year, $month = null, $quarter = null)
    {
        $query->where('period_year', $year);
        if ($month) $query->where('period_month', $month);
        if ($quarter) $query->where('period_quarter', $quarter);
        return $query;
    }

    // Accessors
    public function getEstEnRetardAttribute(): bool
    {
        return $this->date_echeance < now() && !in_array($this->status, ['depose', 'paye']);
    }

    public function getLibelleTypeAttribute(): string
    {
        $labels = [
            'tva' => 'TVA',
            'is' => 'Impôt sur les Sociétés',
            'its' => 'Impôt sur les Traitements et Salaires',
            'cnss' => 'Cotisation CNSS',
            'vps' => 'Versement Patronal sur Salaires',
            'aib' => 'AIB (Acompte IS)',
        ];
        return $labels[$this->tax_type] ?? $this->tax_type;
    }
}
