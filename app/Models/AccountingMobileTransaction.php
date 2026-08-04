<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle représentant une transaction de mobile money.
 *
 * Gère les transactions via les services de paiement mobile (Orange Money,
 * MTN Mobile Money, Wave, etc.). Permet le suivi des transferts,
 * des frais et des soldes avant/après transaction.
 *
 * @property int $id
 * @property int|null $client_id Identifiant du client (entreprise)
 * @property string $reference_transaction Référence de la transaction
 * @property string $operateur Opérateur (Orange Money, MTN, Wave, etc.)
 * @property string $type Type (envoi, reception, depot, retrait)
 * @property string $numero_expediteur Numéro de l'expéditeur
 * @property string $numero_destinataire Numéro du destinataire
 * @property string|null $nom_expediteur Nom de l'expéditeur
 * @property string|null $nom_destinataire Nom du destinataire
 * @property float $montant Montant de la transaction
 * @property float $frais Frais de transaction
 * @property float $montant_net Montant net après frais
 * @property float $solde_avant Solde avant transaction
 * @property float $solde_apres Solde après transaction
 * @property string|null $date_transaction Date et heure de la transaction
 * @property string $statut Statut (reussie, echouee, en_attente)
 * @property string|null $motif Motif de la transaction
 * @property int|null $created_by Identifiant de l'utilisateur créateur
 *
 * @property-read Client|null $client Client (entreprise) associé
 *
 * @table accounting_mobile_transactions
 */
class AccountingMobileTransaction extends Model
{
    use SoftDeletes;
    protected $table = 'accounting_mobile_transactions';
    protected $fillable = ['client_id','reference_transaction','operateur','type','numero_expediteur','numero_destinataire','nom_expediteur','nom_destinataire','montant','frais','montant_net','solde_avant','solde_apres','date_transaction','statut','motif','created_by'];
    protected $casts = ['montant'=>'decimal:2','frais'=>'decimal:2','montant_net'=>'decimal:2','solde_avant'=>'decimal:2','solde_apres'=>'decimal:2','date_transaction'=>'datetime'];
    public function client() { return $this->belongsTo(Client::class); }
    public function scopeForClient($q, $cid) { return $q->where('client_id', $cid); }
}
