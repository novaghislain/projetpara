<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle VatRate - Taux de TVA applicable.
 *
 * Table associée : 'vat_rates' (convention Laravel).
 * Définit les taux de TVA par code, avec comptes comptables de collecte
 * et de déduction associés. Supporte les taux UEMOA par pays.
 * Relations :
 * - client() : appartient à un client (Client).
 * - collectAccount() : compte de collecte (AccountingAccount).
 * - deductAccount() : compte de déduction (AccountingAccount).
 */
class VatRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'code', 'name', 'rate', 'type',
        'collect_account_id', 'deduct_account_id',
        'is_active', 'is_default', 'country_code',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function collectAccount(): BelongsTo
    {
        return $this->belongsTo(AccountingAccount::class, 'collect_account_id');
    }

    public function deductAccount(): BelongsTo
    {
        return $this->belongsTo(AccountingAccount::class, 'deduct_account_id');
    }

    // Taux UEMOA par défaut
    const UEMOA_RATES = [
        'BJ' => [18.00, 9.00, 0.00], // Bénin
        'BF' => [18.00, 9.00, 0.00], // Burkina Faso
        'CI' => [18.00, 9.00, 0.00], // Côte d'Ivoire
        'ML' => [18.00, 9.00, 0.00], // Mali
        'NE' => [19.00, 9.00, 0.00], // Niger
        'SN' => [18.00, 9.00, 0.00], // Sénégal
        'TG' => [18.00, 9.00, 0.00], // Togo
        'GW' => [18.00, 9.00, 0.00], // Guinée-Bissau
    ];
}
