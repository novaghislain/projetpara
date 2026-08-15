<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use App\Models\BankAccount;

class BankStatement extends Model
{
    protected $fillable = [
        'bank_account_id',
        'statement_date',
        'starting_balance',
        'ending_balance',
        'status', // draft, processing, reconciled
    ];

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function lines()
    {
        return $this->hasMany(BankStatementLine::class);
    }
}
