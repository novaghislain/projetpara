<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountingCotisation extends Model
{
    use SoftDeletes;
    protected $table = 'accounting_cotisations';
    protected $fillable = [
        'client_id','tontine_nom','membre_nom','membre_contact',
        'periode','date_echeance','montant','montant_paye','solde',
        'statut','date_paiement','mode_paiement','notes','created_by',
    ];
    protected $casts = [
        'date_echeance' => 'date', 'date_paiement' => 'date',
        'montant' => 'decimal:2', 'montant_paye' => 'decimal:2', 'solde' => 'decimal:2',
    ];
    public function client() { return $this->belongsTo(Client::class); }
    public function scopeForClient($q, $cid) { return $q->where('client_id', $cid); }
}
