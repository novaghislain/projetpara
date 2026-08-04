<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle représentant une transaction bancaire dans le module ERP.
 *
 * Table associée : `erp_transactions` (via convention Laravel)
 *
 * Relations :
 * - Une transaction appartient à un compte bancaire (ErpBankAccount)
 * - Une transaction est créée par un utilisateur (User)
 */
class ErpTransaction extends Model {
    protected $fillable = ['erp_bank_account_id','transaction_date','type','amount','reference','description','created_by'];
    protected $casts = ['transaction_date'=>'date','amount'=>'decimal:2'];
    public function account()  { return $this->belongsTo(ErpBankAccount::class, 'erp_bank_account_id'); }
    public function creator()  { return $this->belongsTo(User::class, 'created_by'); }
}
