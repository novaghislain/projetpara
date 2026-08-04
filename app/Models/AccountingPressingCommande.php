<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle représentant une commande de pressing / blanchisserie.
 *
 * Gère le suivi des commandes de nettoyage de vêtements, avec les
 * informations sur le client, les articles confiés, les dates de
 * dépôt et de retrait, et le suivi des paiements.
 *
 * @property int $id
 * @property int|null $client_id Identifiant du client (établissement de pressing)
 * @property string $numero_commande Numéro de commande
 * @property string $client_nom Nom du client
 * @property string $client_contact Contact du client
 * @property string|null $date_depot Date de dépôt des articles
 * @property string|null $date_retrait_prevu Date de retrait prévue
 * @property string|null $date_retrait Date de retrait effective
 * @property int $nb_articles Nombre d'articles
 * @property string|null $articles Description des articles
 * @property string $type_service Type de service (nettoyage sec, repassage, etc.)
 * @property float $montant_total Montant total
 * @property float $acompte Acompte versé
 * @property float $solde Solde restant
 * @property string $statut Statut (en_cours, pret, retire, annule)
 * @property string|null $notes Notes
 * @property int|null $created_by Identifiant de l'utilisateur créateur
 *
 * @property-read Client|null $client Client (établissement) associé
 *
 * @table accounting_pressing_commandes
 */
class AccountingPressingCommande extends Model
{
    use SoftDeletes;
    protected $table = 'accounting_pressing_commandes';
    protected $fillable = ['client_id','numero_commande','client_nom','client_contact','date_depot','date_retrait_prevu','date_retrait','nb_articles','articles','type_service','montant_total','acompte','solde','statut','notes','created_by'];
    protected $casts = ['date_depot'=>'date','date_retrait_prevu'=>'date','date_retrait'=>'date','nb_articles'=>'integer','montant_total'=>'decimal:2','acompte'=>'decimal:2','solde'=>'decimal:2'];
    public function client() { return $this->belongsTo(Client::class); }
    public function scopeForClient($q, $cid) { return $q->where('client_id', $cid); }
}
