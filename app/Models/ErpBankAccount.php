<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ErpBankAccount extends Model {
    protected $fillable = ['name','type','account_number','initial_balance','is_active'];
    protected $casts = ['initial_balance'=>'decimal:2','is_active'=>'boolean'];
    public function transactions() { return $this->hasMany(ErpTransaction::class); }
}
