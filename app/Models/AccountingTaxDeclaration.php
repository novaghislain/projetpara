<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

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
