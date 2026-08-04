<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle représentant un emballage consigné.
 *
 * Gère le suivi des emballages consignés (cartons, palettes, fûts, etc.)
 * remis à des tiers (clients ou fournisseurs). Permet le suivi des
 * mouvements, des consignations et des retours d'emballages.
 *
 * @property int $id
 * @property int|null $client_id Identifiant du client (entreprise)
 * @property string $type Type d'emballage
 * @property string $tiers_nom Nom du tiers (client ou fournisseur)
 * @property string $tiers_type Type de tiers (client, fournisseur)
 * @property string $produit Produit concerné
 * @property int $quantite Quantité d'emballages
 * @property float $montant_consigne Montant de la consigne
 * @property string|null $date_emission Date d'émission
 * @property string|null $date_retour Date de retour
 * @property string $statut Statut (en_cours, retourne, facture)
 * @property string|null $notes Notes
 * @property int|null $created_by Identifiant de l'utilisateur créateur
 *
 * @property-read Client|null $client Client (entreprise) associé
 * @property-read User|null $creator Utilisateur créateur
 *
 * @table accounting_emballages
 */
class AccountingEmballage extends Model
{
    use SoftDeletes;
    protected $table = 'accounting_emballages';

    protected $fillable = [
        'client_id', 'type', 'tiers_nom', 'tiers_type',
        'produit', 'quantite', 'montant_consigne',
        'date_emission', 'date_retour', 'statut', 'notes',
        'created_by',
    ];

    protected $casts = [
        'quantite' => 'integer',
        'montant_consigne' => 'decimal:2',
        'date_emission' => 'date',
        'date_retour' => 'date',
    ];

    public function client() { return $this->belongsTo(Client::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }

    public function scopeForClient($q, $cid) { return $q->where('client_id', $cid); }
    public function scopeActif($q) { return $q->where('statut', 'en_cours'); }
}
