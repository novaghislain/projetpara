<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountingBudget extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id', 'fiscal_year_id', 'name', 'type', 'status',
        'montant_prevu', 'montant_realise', 'notes',
        'date_debut', 'date_fin',
        'created_by', 'validated_by', 'validated_at',
    ];

    protected function casts(): array
    {
        return [
            'montant_prevu' => 'decimal:2',
            'montant_realise' => 'decimal:2',
            'date_debut' => 'date',
            'date_fin' => 'date',
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

    public function lines(): HasMany
    {
        return $this->hasMany(AccountingBudgetLine::class, 'budget_id');
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
    public function scopeActif($query)
    {
        return $query->where('status', 'actif');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Accessors
    public function getTauxRealisationAttribute(): float
    {
        if ($this->montant_prevu == 0) return 0;
        return round(($this->montant_realise / $this->montant_prevu) * 100, 2);
    }

    public function getEcartAttribute(): float
    {
        return $this->montant_prevu - $this->montant_realise;
    }
}

class AccountingBudgetLine extends Model
{
    use SoftDeletes;

    protected $table = 'accounting_budget_lines';

    protected $fillable = [
        'budget_id', 'account_id', 'label',
        'montant_prevu', 'montant_realise', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'montant_prevu' => 'decimal:2',
            'montant_realise' => 'decimal:2',
        ];
    }

    public function budget(): BelongsTo
    {
        return $this->belongsTo(AccountingBudget::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(AccountingAccount::class);
    }

    public function getTauxRealisationAttribute(): float
    {
        if ($this->montant_prevu == 0) return 0;
        return round(($this->montant_realise / $this->montant_prevu) * 100, 2);
    }
}
