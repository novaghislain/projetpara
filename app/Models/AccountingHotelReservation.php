<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle représentant une réservation hôtelière.
 *
 * Gère les réservations de chambres dans le module hôtelier.
 * Chaque réservation est liée à un client (établissement) et à
 * une chambre spécifique, avec suivi des dates, du nombre de
 * personnes et du statut de la réservation.
 *
 * @property int $id
 * @property int|null $client_id Identifiant du client (établissement)
 * @property string $numero_reservation Numéro de réservation
 * @property int|null $chambre_id Identifiant de la chambre réservée
 * @property string $client_nom Nom du client (voyageur)
 * @property string $client_contact Contact du client
 * @property string|null $client_email Email du client
 * @property string|null $date_arrivee Date d'arrivée
 * @property string|null $date_depart Date de départ
 * @property int $nb_nuitees Nombre de nuitées
 * @property int $nb_adultes Nombre d'adultes
 * @property int $nb_enfants Nombre d'enfants
 * @property float $montant_total Montant total de la réservation
 * @property float $acompte Acompte versé
 * @property float $solde Solde restant
 * @property string $statut Statut (confirmee, en_attente, annulee)
 * @property string $source Source de la réservation
 * @property string|null $notes Notes
 * @property int|null $created_by Identifiant de l'utilisateur créateur
 *
 * @property-read Client|null $client Client (établissement) associé
 * @property-read AccountingHotelChambre|null $chambre Chambre réservée
 *
 * @table accounting_hotel_reservations
 */
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
