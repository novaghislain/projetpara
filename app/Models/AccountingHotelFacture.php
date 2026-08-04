<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle représentant une facture hôtelière.
 *
 * Gère la facturation des séjours clients dans le module hôtelier.
 * Inclut le calcul des nuitées, de la TVA, de la taxe de séjour,
 * des remises et des services supplémentaires.
 *
 * @property int $id
 * @property int|null $client_id Identifiant du client (établissement)
 * @property string $numero_facture Numéro de la facture
 * @property string $type Type de facture
 * @property string $client_nom Nom du client (voyageur)
 * @property string $client_contact Contact du client
 * @property string|null $chambre Chambre attribuée
 * @property string|null $date_arrivee Date d'arrivée
 * @property string|null $date_depart Date de départ
 * @property int $nb_nuitees Nombre de nuitées
 * @property float $prix_nuitee Prix par nuitée
 * @property float $montant_ht Montant hors taxes
 * @property float $tva TVA
 * @property float $taxe_sejour Taxe de séjour
 * @property float $remise Remise accordée
 * @property float $montant_ttc Montant toutes taxes comprises
 * @property float $montant_paye Montant payé
 * @property float $solde Solde restant
 * @property string $statut Statut (payee, impayee, partielle)
 * @property string|null $mode_paiement Mode de paiement
 * @property array|null $services_supplementaires Services supplémentaires
 * @property string|null $notes Notes
 * @property int|null $created_by Identifiant de l'utilisateur créateur
 *
 * @property-read Client|null $client Client (établissement) associé
 *
 * @table accounting_hotel_factures
 */
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
