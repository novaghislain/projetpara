<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle représentant un groupe de tontine.
 *
 * Gère les groupes d'épargne rotative (tontines). Chaque tontine
 * est liée à un client (entreprise) et définit les règles de
 * cotisation : montant, fréquence, nombre de membres, etc.
 *
 * @property int $id
 * @property int|null $client_id Identifiant du client (entreprise)
 * @property string $nom_groupe Nom du groupe de tontine
 * @property string|null $description Description de la tontine
 * @property int $nb_membres Nombre de membres
 * @property float $montant_cotisation Montant de la cotisation par membre
 * @property string $frequence Fréquence des cotisations (hebdomadaire, mensuelle, etc.)
 * @property float $montant_caisse Montant total dans la caisse
 * @property string|null $date_creation Date de création du groupe
 * @property string $statut Statut (active, suspendue, cloturee)
 * @property string|null $regles Règles de la tontine
 * @property int|null $created_by Identifiant de l'utilisateur créateur
 *
 * @property-read Client|null $client Client (entreprise) associé
 *
 * @table accounting_tontines
 */
class AccountingTontine extends Model
{
    use SoftDeletes;
    protected $table = 'accounting_tontines';
    protected $fillable = ['client_id','nom_groupe','description','nb_membres','montant_cotisation','frequence','montant_caisse','date_creation','statut','regles','created_by'];
    protected $casts = ['nb_membres'=>'integer','montant_cotisation'=>'decimal:2','montant_caisse'=>'decimal:2','date_creation'=>'date'];
    public function client() { return $this->belongsTo(Client::class); }
    public function scopeForClient($q, $cid) { return $q->where('client_id', $cid); }
}
