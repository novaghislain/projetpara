<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountingMorgueDepot extends Model
{
    use SoftDeletes;
    protected $table = 'accounting_morgue_depots';
    protected $fillable = ['client_id','numero_dossier','defunt_nom','defunt_prenom','date_deces','date_depot','date_sortie','famille_contact','famille_nom','type_conservation','nb_jours','tarif_journalier','montant_total','montant_paye','solde','statut','notes','created_by'];
    protected $casts = ['date_deces'=>'date','date_depot'=>'date','date_sortie'=>'date','nb_jours'=>'integer','tarif_journalier'=>'decimal:2','montant_total'=>'decimal:2','montant_paye'=>'decimal:2','solde'=>'decimal:2'];
    public function client() { return $this->belongsTo(Client::class); }
    public function scopeForClient($q, $cid) { return $q->where('client_id', $cid); }
}
