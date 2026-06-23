<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountingMorgueFacture extends Model
{
    use SoftDeletes;
    protected $table = 'accounting_morgue_factures';
    protected $fillable = ['client_id','numero_facture','depot_id','client_nom','defunt_nom','type_prestation','nb_jours','montant_ht','tva','montant_ttc','montant_paye','solde','statut','notes','created_by'];
    protected $casts = ['nb_jours'=>'integer','montant_ht'=>'decimal:2','tva'=>'decimal:2','montant_ttc'=>'decimal:2','montant_paye'=>'decimal:2','solde'=>'decimal:2'];
    public function client() { return $this->belongsTo(Client::class); }
    public function depot() { return $this->belongsTo(AccountingMorgueDepot::class, 'depot_id'); }
    public function scopeForClient($q, $cid) { return $q->where('client_id', $cid); }
}
