<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle représentant un bien immobilier destiné à la location.
 *
 * Gère le parc immobilier dans le module de location. Chaque bien
 * est lié à un client (propriétaire) et contient les informations
 * sur le loyer, les charges, la caution et le locataire actuel.
 *
 * @property int $id
 * @property int|null $client_id Identifiant du client (propriétaire)
 * @property string $reference_bien Référence unique du bien
 * @property string $designation Désignation du bien
 * @property string $type Type de bien (appartement, maison, local, terrain)
 * @property string $adresse Adresse complète
 * @property string $ville Ville
 * @property string|null $quartier Quartier
 * @property float|null $surface Surface en m²
 * @property int $nb_pieces Nombre de pièces
 * @property float $loyer_mensuel Loyer mensuel
 * @property float $charges_mensuelles Charges mensuelles
 * @property float $caution Montant de la caution
 * @property string $statut Statut (libre, occupe, en_travaux)
 * @property string|null $locataire_actuel Nom du locataire actuel
 * @property string|null $date_debut_bail Date de début du bail
 * @property string|null $date_fin_bail Date de fin du bail
 * @property string|null $notes Notes
 * @property bool $is_active Indique si le bien est actif
 * @property int|null $created_by Identifiant de l'utilisateur créateur
 *
 * @property-read Client|null $client Client (propriétaire) associé
 *
 * @table accounting_location_biens
 */
class AccountingLocationBien extends Model
{
    use SoftDeletes;
    protected $table = 'accounting_location_biens';
    protected $fillable = ['client_id','reference_bien','designation','type','adresse','ville','quartier','surface','nb_pieces','loyer_mensuel','charges_mensuelles','caution','statut','locataire_actuel','date_debut_bail','date_fin_bail','notes','is_active','created_by'];
    protected $casts = ['loyer_mensuel'=>'decimal:2','charges_mensuelles'=>'decimal:2','caution'=>'decimal:2','surface'=>'decimal:2','date_debut_bail'=>'date','date_fin_bail'=>'date','is_active'=>'boolean'];
    public function client() { return $this->belongsTo(Client::class); }
    public function scopeForClient($q, $cid) { return $q->where('client_id', $cid); }
}
