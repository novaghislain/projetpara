<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle représentant une cotisation de tontine.
 *
 * Gère les cotisations versées par les membres d'une tontine.
 * Chaque cotisation est liée à un client (entreprise) et à une tontine
 * spécifique. Elle assure le suivi des échéances, des paiements et
 * des soldes restants pour chaque membre.
 *
 * @property int $id
 * @property int|null $client_id Identifiant du client (entreprise)
 * @property string $tontine_nom Nom de la tontine
 * @property string $membre_nom Nom du membre
 * @property string $membre_contact Contact du membre
 * @property string $periode Période concernée (mois, année)
 * @property string|null $date_echeance Date d'échéance
 * @property float $montant Montant de la cotisation
 * @property float $montant_paye Montant payé
 * @property float $solde Solde restant
 * @property string $statut Statut (paye, impaye, partiel)
 * @property string|null $date_paiement Date du paiement
 * @property string|null $mode_paiement Mode de paiement
 * @property string|null $notes Notes additionnelles
 * @property int|null $created_by Identifiant de l'utilisateur créateur
 *
 * @property-read Client|null $client Client (entreprise) associé
 *
 * @table accounting_cotisations
 */
class AccountingCotisation extends Model
{
    use SoftDeletes;
    protected $table = 'accounting_cotisations';
    protected $fillable = [
        'client_id','tontine_nom','membre_nom','membre_contact',
        'periode','date_echeance','montant','montant_paye','solde',
        'statut','date_paiement','mode_paiement','notes','created_by',
    ];
    protected $casts = [
        'date_echeance' => 'date', 'date_paiement' => 'date',
        'montant' => 'decimal:2', 'montant_paye' => 'decimal:2', 'solde' => 'decimal:2',
    ];
    public function client() { return $this->belongsTo(Client::class); }
    public function scopeForClient($q, $cid) { return $q->where('client_id', $cid); }
}
