<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountingGrilleTarifaire extends Model
{
    use SoftDeletes;
    protected $table = 'accounting_grilles_tarifaires';
    protected $fillable = ['client_id','code','designation','categorie','unite','prix_unitaire','tva','remise_max','date_validite_debut','date_validite_fin','is_active','notes','created_by'];
    protected $casts = ['prix_unitaire'=>'decimal:2','tva'=>'decimal:2','remise_max'=>'decimal:2','date_validite_debut'=>'date','date_validite_fin'=>'date','is_active'=>'boolean'];
    public function client() { return $this->belongsTo(Client::class); }
    public function scopeForClient($q, $cid) { return $q->where('client_id', $cid); }
}
