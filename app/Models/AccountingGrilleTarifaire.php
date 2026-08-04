<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle représentant une grille tarifaire.
 *
 * Définit les prix unitaires, taux de TVA et remises maximales
 * pour différents produits ou prestations. Chaque grille est liée
 * à un client (entreprise) et peut avoir une période de validité.
 *
 * @property int $id
 * @property int|null $client_id Identifiant du client (entreprise)
 * @property string $code Code de la grille tarifaire
 * @property string $designation Désignation ou libellé
 * @property string $categorie Catégorie de la grille
 * @property string $unite Unité de mesure
 * @property float $prix_unitaire Prix unitaire
 * @property float $tva Taux de TVA applicable
 * @property float $remise_max Remise maximale autorisée
 * @property string|null $date_validite_debut Date de début de validité
 * @property string|null $date_validite_fin Date de fin de validité
 * @property bool $is_active Indique si la grille est active
 * @property string|null $notes Notes
 * @property int|null $created_by Identifiant de l'utilisateur créateur
 *
 * @property-read Client|null $client Client (entreprise) associé
 *
 * @table accounting_grilles_tarifaires
 */
class AccountingGrilleTarifaire extends Model
{
    use SoftDeletes;
    protected $table = 'accounting_grilles_tarifaires';
    protected $fillable = ['client_id','code','designation','categorie','unite','prix_unitaire','tva','remise_max','date_validite_debut','date_validite_fin','is_active','notes','created_by'];
    protected $casts = ['prix_unitaire'=>'decimal:2','tva'=>'decimal:2','remise_max'=>'decimal:2','date_validite_debut'=>'date','date_validite_fin'=>'date','is_active'=>'boolean'];
    public function client() { return $this->belongsTo(Client::class); }
    public function scopeForClient($q, $cid) { return $q->where('client_id', $cid); }
}
