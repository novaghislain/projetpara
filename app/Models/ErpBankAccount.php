<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle représentant un compte bancaire dans le module ERP.
 *
 * Table associée : `erp_bank_accounts` (via convention Laravel)
 *
 * Relations :
 * - Un compte bancaire peut avoir plusieurs transactions (ErpTransaction)
 */
class ErpBankAccount extends Model {
    protected $fillable = ['name','type','account_number','initial_balance','is_active'];
    protected $casts = ['initial_balance'=>'decimal:2','is_active'=>'boolean'];
    public function transactions() { return $this->hasMany(ErpTransaction::class); }
}
