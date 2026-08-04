<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle représentant une commission.
 *
 * Gère les commissions versées à des agents commerciaux ou intermédiaires.
 * Chaque commission est liée à un client (entreprise) et inclut le calcul
 * du montant de la commission à partir d'une base et d'un taux, avec
 * gestion de la TVA et suivi des paiements.
 *
 * @property int $id
 * @property int|null $client_id Identifiant du client (entreprise)
 * @property string $numero_commission Numéro unique de la commission
 * @property string $type Type de commission
 * @property string $agent_nom Nom de l'agent
 * @property string $agent_contact Contact de l'agent
 * @property float $montant_base Montant de base pour le calcul
 * @property float $taux_commission Taux de commission appliqué (en %)
 * @property float $montant_commission Montant de la commission calculée
 * @property float $tva TVA sur la commission
 * @property float $montant_net Montant net après TVA
 * @property float $montant_paye Montant déjà payé
 * @property float $solde Solde restant dû
 * @property string|null $date_operation Date de l'opération
 * @property string|null $date_paiement Date du paiement
 * @property string $statut Statut de la commission
 * @property string|null $description Description ou notes
 * @property int|null $created_by Identifiant de l'utilisateur créateur
 *
 * @property-read Client|null $client Client (entreprise) associé
 *
 * @table accounting_commissions
 */
class AccountingCommission extends Model
{
    use SoftDeletes;
    protected $table = 'accounting_commissions';
    protected $fillable = ['client_id','numero_commission','type','agent_nom','agent_contact','montant_base','taux_commission','montant_commission','tva','montant_net','montant_paye','solde','date_operation','date_paiement','statut','description','created_by'];
    protected $casts = ['montant_base'=>'decimal:2','taux_commission'=>'decimal:2','montant_commission'=>'decimal:2','tva'=>'decimal:2','montant_net'=>'decimal:2','montant_paye'=>'decimal:2','solde'=>'decimal:2','date_operation'=>'date','date_paiement'=>'date'];
    public function client() { return $this->belongsTo(Client::class); }
    public function scopeForClient($q, $cid) { return $q->where('client_id', $cid); }
}
