<?php

namespace Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Accounting\Database\Factories\JournalEntryFactory;

class JournalEntry extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['date', 'reference', 'description', 'amount_debit', 'amount_credit', 'account_id'];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    // protected static function newFactory(): JournalEntryFactory
    // {
    //     // return JournalEntryFactory::new();
    // }
}
