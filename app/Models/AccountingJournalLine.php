<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Auditable;

/**
 * Modèle représentant une ligne d'écriture comptable.
 *
 * Chaque ligne appartient à un journal comptable (AccountingJournal)
 * et est liée à un compte comptable (AccountingAccount).
 * Une ligne contient un montant au débit OU au crédit (jamais les deux
 * pour une même ligne) et peut inclure des informations de TVA, d'AIB,
 * d'échéance et de lettres de relance (lettrage).
 *
 * @property int $id
 * @property int $journal_id Identifiant du journal associé
 * @property int|null $account_id Identifiant du compte comptable
 * @property string $label Libellé de la ligne
 * @property float $debit Montant au débit
 * @property float $credit Montant au crédit
 * @property string|null $tva_code Code TVA associé
 * @property float|null $tva_rate Taux de TVA
 * @property float|null $tva_amount Montant de TVA
 * @property string|null $tva_type Type de TVA (collectee, deductible)
 * @property float|null $aib_rate Taux d'AIB (Acompte Impôt sur les Bénéfices)
 * @property float|null $aib_amount Montant d'AIB
 * @property string|null $due_date Date d'échéance
 * @property string|null $lettering_code Code de lettrage
 * @property int|null $cost_center_id Identifiant du centre de coût
 *
 * @property-read AccountingJournal $journal Journal associé
 * @property-read AccountingAccount|null $account Compte comptable associé
 * @property-read CostCenter|null $costCenter Centre de coût associé
 *
 * @table accounting_journal_lines
 */
class AccountingJournalLine extends Model
{
    use Auditable;
    protected $fillable = [
        'journal_id', 'account_id', 'label', 'debit', 'credit',
        'tva_code', 'tva_rate', 'tva_amount', 'tva_type',
        'aib_rate', 'aib_amount',
        'due_date', 'lettering_code', 'cost_center_id',
    ];

    protected function casts(): array
    {
        return [
            'debit' => 'decimal:2',
            'credit' => 'decimal:2',
            'tva_rate' => 'decimal:2',
            'tva_amount' => 'decimal:2',
            'aib_rate' => 'decimal:2',
            'aib_amount' => 'decimal:2',
            'due_date' => 'date',
        ];
    }

    // Relations
    public function journal(): BelongsTo
    {
        return $this->belongsTo(AccountingJournal::class, 'journal_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(AccountingAccount::class, 'account_id');
    }

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class, 'cost_center_id');
    }
}
