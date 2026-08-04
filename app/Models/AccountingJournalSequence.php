<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle représentant une séquence de numérotation pour les journaux.
 *
 * Gère la numérotation automatique et séquentielle des pièces comptables
 * par type de journal, client et exercice fiscal. Chaque séquence
 * est incrémentée à chaque nouvelle écriture pour générer une
 * référence unique (ex: VTE-2024-0001).
 *
 * @property int $id
 * @property int $client_id Identifiant du client (entreprise)
 * @property string $journal_type Type de journal
 * @property int $fiscal_year_id Identifiant de l'exercice fiscal
 * @property int $last_number Dernier numéro utilisé
 * @property string $prefix Préfixe de la référence
 *
 * @property-read User|null $client Client associé
 * @property-read FiscalYear|null $fiscalYear Exercice fiscal associé
 *
 * @table accounting_journal_sequences
 */
class AccountingJournalSequence extends Model
{
    protected $fillable = [
        'client_id', 'journal_type', 'fiscal_year_id',
        'last_number', 'prefix',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    /**
     * Get next sequential number for this journal type.
     */
    public static function getNextNumber(int $clientId, string $journalType, int $fiscalYearId, string $prefix = ''): string
    {
        $seq = static::firstOrCreate(
            [
                'client_id' => $clientId,
                'journal_type' => $journalType,
                'fiscal_year_id' => $fiscalYearId,
            ],
            [
                'last_number' => 0,
                'prefix' => $prefix,
            ]
        );

        $seq->increment('last_number');

        $num = str_pad((string) $seq->last_number, 4, '0', STR_PAD_LEFT);
        $pfx = $seq->prefix ?: strtoupper($journalType);

        return "{$pfx}-{$fiscalYearId}-{$num}";
    }
}
