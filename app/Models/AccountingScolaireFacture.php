<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountingScolaireFacture extends Model
{
    use SoftDeletes;
    protected $table = 'accounting_scolaire_factures';
    protected $fillable = [
        'client_id','numero_facture','annee_scolaire','eleve_nom','eleve_prenom',
        'classe','matricule','type_frais','periode','montant_du','remise',
        'montant_net','montant_paye','solde','statut','date_echeance',
        'date_paiement','mode_paiement','notes','created_by',
    ];
    protected $casts = [
        'date_echeance' => 'date', 'date_paiement' => 'date',
        'montant_du' => 'decimal:2', 'remise' => 'decimal:2',
        'montant_net' => 'decimal:2', 'montant_paye' => 'decimal:2', 'solde' => 'decimal:2',
    ];
    public function client() { return $this->belongsTo(Client::class); }
    public function scopeForClient($q, $cid) { return $q->where('client_id', $cid); }
}
