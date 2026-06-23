<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountingQuittance extends Model
{
    use SoftDeletes;
    protected $table = 'accounting_quittances';
    protected $fillable = [
        'client_id','numero_quittance','bien','locataire_nom','locataire_contact',
        'periode','date_debut','date_fin','loyer_ht','charges','tva',
        'montant_total','montant_paye','solde','statut','date_echeance',
        'date_paiement','mode_paiement','notes','created_by',
    ];
    protected $casts = [
        'date_debut' => 'date', 'date_fin' => 'date', 'date_echeance' => 'date', 'date_paiement' => 'date',
        'loyer_ht' => 'decimal:2', 'charges' => 'decimal:2', 'tva' => 'decimal:2',
        'montant_total' => 'decimal:2', 'montant_paye' => 'decimal:2', 'solde' => 'decimal:2',
    ];
    public function client() { return $this->belongsTo(Client::class); }
    public function scopeForClient($q, $cid) { return $q->where('client_id', $cid); }
}
