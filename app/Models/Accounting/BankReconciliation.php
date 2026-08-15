<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use App\Models\JournalEntry;

class BankReconciliation extends Model
{
    protected $fillable = [
        'bank_statement_line_id',
        'journal_entry_id',
        'amount_reconciled',
    ];

    public function statementLine()
    {
        return $this->belongsTo(BankStatementLine::class, 'bank_statement_line_id');
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }
}
