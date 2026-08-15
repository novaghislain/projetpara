<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class BankStatementLine extends Model
{
    protected $fillable = [
        'bank_statement_id',
        'date',
        'label',
        'amount',
        'reference',
        'is_reconciled',
    ];

    public function statement()
    {
        return $this->belongsTo(BankStatement::class, 'bank_statement_id');
    }

    public function reconciliations()
    {
        return $this->hasMany(BankReconciliation::class, 'bank_statement_line_id');
    }
}
