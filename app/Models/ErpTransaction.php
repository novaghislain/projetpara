<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ErpTransaction extends Model {
    protected $fillable = ['erp_bank_account_id','transaction_date','type','amount','reference','description','created_by'];
    protected $casts = ['transaction_date'=>'date','amount'=>'decimal:2'];
    public function account()  { return $this->belongsTo(ErpBankAccount::class, 'erp_bank_account_id'); }
    public function creator()  { return $this->belongsTo(User::class, 'created_by'); }
}
