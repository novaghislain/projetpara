<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountingPressingCommande extends Model
{
    use SoftDeletes;
    protected $table = 'accounting_pressing_commandes';
    protected $fillable = ['client_id','numero_commande','client_nom','client_contact','date_depot','date_retrait_prevu','date_retrait','nb_articles','articles','type_service','montant_total','acompte','solde','statut','notes','created_by'];
    protected $casts = ['date_depot'=>'date','date_retrait_prevu'=>'date','date_retrait'=>'date','nb_articles'=>'integer','montant_total'=>'decimal:2','acompte'=>'decimal:2','solde'=>'decimal:2'];
    public function client() { return $this->belongsTo(Client::class); }
    public function scopeForClient($q, $cid) { return $q->where('client_id', $cid); }
}
