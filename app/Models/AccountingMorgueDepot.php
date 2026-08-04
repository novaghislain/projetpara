<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle représentant un dépôt à la morgue.
 *
 * Gère le suivi des corps déposés à la morgue, incluant les informations
 * sur le défunt, les dates de dépôt et de sortie, le type de conservation
 * et la facturation calculée sur une base journalière.
 *
 * @property int $id
 * @property int|null $client_id Identifiant du client (établissement funéraire)
 * @property string $numero_dossier Numéro du dossier
 * @property string $defunt_nom Nom du défunt
 * @property string|null $defunt_prenom Prénom du défunt
 * @property string|null $date_deces Date du décès
 * @property string|null $date_depot Date de dépôt à la morgue
 * @property string|null $date_sortie Date de sortie de la morgue
 * @property string|null $famille_contact Contact de la famille
 * @property string|null $famille_nom Nom de la famille
 * @property string $type_conservation Type de conservation (normale, refrigeration)
 * @property int $nb_jours Nombre de jours de conservation
 * @property float $tarif_journalier Tarif par jour
 * @property float $montant_total Montant total
 * @property float $montant_paye Montant payé
 * @property float $solde Solde restant
 * @property string $statut Statut (en_cours, sorti, facture)
 * @property string|null $notes Notes
 * @property int|null $created_by Identifiant de l'utilisateur créateur
 *
 * @property-read Client|null $client Client (établissement) associé
 *
 * @table accounting_morgue_depots
 */
class AccountingMorgueDepot extends Model
{
    use SoftDeletes;
    protected $table = 'accounting_morgue_depots';
    protected $fillable = ['client_id','numero_dossier','defunt_nom','defunt_prenom','date_deces','date_depot','date_sortie','famille_contact','famille_nom','type_conservation','nb_jours','tarif_journalier','montant_total','montant_paye','solde','statut','notes','created_by'];
    protected $casts = ['date_deces'=>'date','date_depot'=>'date','date_sortie'=>'date','nb_jours'=>'integer','tarif_journalier'=>'decimal:2','montant_total'=>'decimal:2','montant_paye'=>'decimal:2','solde'=>'decimal:2'];
    public function client() { return $this->belongsTo(Client::class); }
    public function scopeForClient($q, $cid) { return $q->where('client_id', $cid); }
}
