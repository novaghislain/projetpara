<?php

namespace Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Accounting\Database\Factories\AccountFactory;

class Account extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['account_number', 'name', 'type', 'is_active'];

    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class);
    }

    // protected static function newFactory(): AccountFactory
    // {
    //     // return AccountFactory::new();
    // }
}
