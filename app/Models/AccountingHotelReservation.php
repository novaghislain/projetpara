<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountingHotelReservation extends Model
{
    use SoftDeletes;
    protected $table = 'accounting_hotel_reservations';
    protected $fillable = ['client_id','numero_reservation','chambre_id','client_nom','client_contact','client_email','date_arrivee','date_depart','nb_nuitees','nb_adultes','nb_enfants','montant_total','acompte','solde','statut','source','notes','created_by'];
    protected $casts = ['date_arrivee'=>'date','date_depart'=>'date','nb_nuitees'=>'integer','montant_total'=>'decimal:2','acompte'=>'decimal:2','solde'=>'decimal:2'];
    public function client() { return $this->belongsTo(Client::class); }
    public function chambre() { return $this->belongsTo(AccountingHotelChambre::class); }
    public function scopeForClient($q, $cid) { return $q->where('client_id', $cid); }
}
