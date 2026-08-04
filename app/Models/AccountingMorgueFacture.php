<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle représentant une facture de morgue.
 *
 * Gère la facturation des prestations funéraires. Chaque facture est
 * liée à un dépôt (AccountingMorgueDepot) et récapitule les frais
 * de conservation et autres prestations avec calcul de la TVA.
 *
 * @property int $id
 * @property int|null $client_id Identifiant du client (établissement funéraire)
 * @property string $numero_facture Numéro de la facture
 * @property int|null $depot_id Identifiant du dépôt associé
 * @property string $client_nom Nom du client facturé
 * @property string $defunt_nom Nom du défunt
 * @property string $type_prestation Type de prestation
 * @property int $nb_jours Nombre de jours
 * @property float $montant_ht Montant hors taxes
 * @property float $tva TVA
 * @property float $montant_ttc Montant toutes taxes comprises
 * @property float $montant_paye Montant payé
 * @property float $solde Solde restant
 * @property string $statut Statut (payee, impayee, partielle)
 * @property string|null $notes Notes
 * @property int|null $created_by Identifiant de l'utilisateur créateur
 *
 * @property-read Client|null $client Client (établissement) associé
 * @property-read AccountingMorgueDepot|null $depot Dépôt associé
 *
 * @table accounting_morgue_factures
 */
class AccountingMorgueFacture extends Model
{
    use SoftDeletes;
    protected $table = 'accounting_morgue_factures';
    protected $fillable = ['client_id','numero_facture','depot_id','client_nom','defunt_nom','type_prestation','nb_jours','montant_ht','tva','montant_ttc','montant_paye','solde','statut','notes','created_by'];
    protected $casts = ['nb_jours'=>'integer','montant_ht'=>'decimal:2','tva'=>'decimal:2','montant_ttc'=>'decimal:2','montant_paye'=>'decimal:2','solde'=>'decimal:2'];
    public function client() { return $this->belongsTo(Client::class); }
    public function depot() { return $this->belongsTo(AccountingMorgueDepot::class, 'depot_id'); }
    public function scopeForClient($q, $cid) { return $q->where('client_id', $cid); }
}
