<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountingMobileTransaction extends Model
{
    use SoftDeletes;
    protected $table = 'accounting_mobile_transactions';
    protected $fillable = ['client_id','reference_transaction','operateur','type','numero_expediteur','numero_destinataire','nom_expediteur','nom_destinataire','montant','frais','montant_net','solde_avant','solde_apres','date_transaction','statut','motif','created_by'];
    protected $casts = ['montant'=>'decimal:2','frais'=>'decimal:2','montant_net'=>'decimal:2','solde_avant'=>'decimal:2','solde_apres'=>'decimal:2','date_transaction'=>'datetime'];
    public function client() { return $this->belongsTo(Client::class); }
    public function scopeForClient($q, $cid) { return $q->where('client_id', $cid); }
}
