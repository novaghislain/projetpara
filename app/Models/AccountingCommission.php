<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountingCommission extends Model
{
    use SoftDeletes;
    protected $table = 'accounting_commissions';
    protected $fillable = ['client_id','numero_commission','type','agent_nom','agent_contact','montant_base','taux_commission','montant_commission','tva','montant_net','montant_paye','solde','date_operation','date_paiement','statut','description','created_by'];
    protected $casts = ['montant_base'=>'decimal:2','taux_commission'=>'decimal:2','montant_commission'=>'decimal:2','tva'=>'decimal:2','montant_net'=>'decimal:2','montant_paye'=>'decimal:2','solde'=>'decimal:2','date_operation'=>'date','date_paiement'=>'date'];
    public function client() { return $this->belongsTo(Client::class); }
    public function scopeForClient($q, $cid) { return $q->where('client_id', $cid); }
}
