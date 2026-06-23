<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountingHotelFacture extends Model
{
    use SoftDeletes;
    protected $table = 'accounting_hotel_factures';
    protected $fillable = [
        'client_id','numero_facture','type','client_nom','client_contact',
        'chambre','date_arrivee','date_depart','nb_nuitees','prix_nuitee',
        'montant_ht','tva','taxe_sejour','remise','montant_ttc','montant_paye',
        'solde','statut','mode_paiement','services_supplementaires','notes','created_by',
    ];
    protected $casts = [
        'date_arrivee' => 'date', 'date_depart' => 'date',
        'nb_nuitees' => 'integer', 'montant_ht' => 'decimal:2',
        'tva' => 'decimal:2', 'taxe_sejour' => 'decimal:2',
        'remise' => 'decimal:2', 'montant_ttc' => 'decimal:2',
        'montant_paye' => 'decimal:2', 'solde' => 'decimal:2',
        'services_supplementaires' => 'array',
    ];
    public function client() { return $this->belongsTo(Client::class); }
    public function scopeForClient($q, $cid) { return $q->where('client_id', $cid); }
}
