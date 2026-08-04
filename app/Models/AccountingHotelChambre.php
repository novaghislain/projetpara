<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle représentant une chambre d'hôtel.
 *
 * Gère l'inventaire des chambres pour le module hôtelier.
 * Chaque chambre est liée à un client (établissement hôtelier)
 * et contient les informations sur son type, sa catégorie,
 * son prix, sa capacité et ses équipements.
 *
 * @property int $id
 * @property int|null $client_id Identifiant du client (établissement)
 * @property string $numero_chambre Numéro de la chambre
 * @property string $type Type de chambre (simple, double, suite, etc.)
 * @property string $categorie Catégorie (standard, deluxe, etc.)
 * @property float $prix_nuitee Prix par nuitée
 * @property int $capacite Capacité d'accueil (nombre de personnes)
 * @property int $etage Numéro d'étage
 * @property string $statut Statut (disponible, occupee, maintenance)
 * @property array|null $equipements Liste des équipements
 * @property string|null $notes Notes
 * @property bool $is_active Indique si la chambre est active
 * @property int|null $created_by Identifiant de l'utilisateur créateur
 *
 * @property-read Client|null $client Client (établissement) associé
 *
 * @table accounting_hotel_chambres
 */
class AccountingHotelChambre extends Model
{
    use SoftDeletes;
    protected $table = 'accounting_hotel_chambres';
    protected $fillable = ['client_id','numero_chambre','type','categorie','prix_nuitee','capacite','etage','statut','equipements','notes','is_active','created_by'];
    protected $casts = ['prix_nuitee'=>'decimal:2','equipements'=>'array','is_active'=>'boolean'];
    public function client() { return $this->belongsTo(Client::class); }
    public function scopeForClient($q, $cid) { return $q->where('client_id', $cid); }
}
