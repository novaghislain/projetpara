<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle représentant une quittance de loyer.
 *
 * Gère les quittances émises pour les locations immobilières. Chaque
 * quittance est liée à un client (propriétaire) et récapitule le loyer,
 * les charges, la TVA et le suivi des paiements pour une période donnée.
 *
 * @property int $id
 * @property int|null $client_id Identifiant du client (propriétaire)
 * @property string $numero_quittance Numéro de la quittance
 * @property string $bien Référence du bien loué
 * @property string $locataire_nom Nom du locataire
 * @property string $locataire_contact Contact du locataire
 * @property string $periode Période concernée (mois/année)
 * @property string $date_debut Date de début de période
 * @property string $date_fin Date de fin de période
 * @property float $loyer_ht Loyer hors taxes
 * @property float $charges Charges locatives
 * @property float $tva TVA
 * @property float $montant_total Montant total
 * @property float $montant_paye Montant payé
 * @property float $solde Solde restant
 * @property string $statut Statut (payee, impayee, partielle)
 * @property string|null $date_echeance Date d'échéance
 * @property string|null $date_paiement Date de paiement
 * @property string|null $mode_paiement Mode de paiement
 * @property string|null $notes Notes
 * @property int|null $created_by Identifiant de l'utilisateur créateur
 *
 * @property-read Client|null $client Client (propriétaire) associé
 *
 * @table accounting_quittances
 */
class AccountingQuittance extends Model
{
    use SoftDeletes;
    protected $table = 'accounting_quittances';
    protected $fillable = [
        'client_id','numero_quittance','bien','locataire_nom','locataire_contact',
        'periode','date_debut','date_fin','loyer_ht','charges','tva',
        'montant_total','montant_paye','solde','statut','date_echeance',
        'date_paiement','mode_paiement','notes','created_by',
    ];
    protected $casts = [
        'date_debut' => 'date', 'date_fin' => 'date', 'date_echeance' => 'date', 'date_paiement' => 'date',
        'loyer_ht' => 'decimal:2', 'charges' => 'decimal:2', 'tva' => 'decimal:2',
        'montant_total' => 'decimal:2', 'montant_paye' => 'decimal:2', 'solde' => 'decimal:2',
    ];
    public function client() { return $this->belongsTo(Client::class); }
    public function scopeForClient($q, $cid) { return $q->where('client_id', $cid); }
}
