<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountingLocationBien extends Model
{
    use SoftDeletes;
    protected $table = 'accounting_location_biens';
    protected $fillable = ['client_id','reference_bien','designation','type','adresse','ville','quartier','surface','nb_pieces','loyer_mensuel','charges_mensuelles','caution','statut','locataire_actuel','date_debut_bail','date_fin_bail','notes','is_active','created_by'];
    protected $casts = ['loyer_mensuel'=>'decimal:2','charges_mensuelles'=>'decimal:2','caution'=>'decimal:2','surface'=>'decimal:2','date_debut_bail'=>'date','date_fin_bail'=>'date','is_active'=>'boolean'];
    public function client() { return $this->belongsTo(Client::class); }
    public function scopeForClient($q, $cid) { return $q->where('client_id', $cid); }
}
