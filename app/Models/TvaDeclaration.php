<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle TvaDeclaration - Déclaration de TVA (ancien système).
 *
 * Table associée : 'tva_declarations' (convention Laravel).
 * Enregistre les déclarations de TVA avec les montants collectés/déductibles,
 * le solde net, et le suivi des statuts (soumis, approuvé, payé).
 * Relations :
 * - client() : appartient à un client (User) [via client_id].
 * - fiscalYear() : appartient à un exercice fiscal (FiscalYear).
 * - createdBy() : appartient à l'utilisateur (User) qui a créé la déclaration.
 */
class TvaDeclaration extends Model
{
    protected $fillable = [
        'client_id', 'fiscal_year_id', 'period', 'type',
        'tva_collected', 'tva_deductible', 'tva_net', 'details',
        'status', 'submitted_at', 'approved_at', 'paid_at',
        'created_by', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'tva_collected' => 'decimal:2',
            'tva_deductible' => 'decimal:2',
            'tva_net' => 'decimal:2',
            'details' => 'json',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
