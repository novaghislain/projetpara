<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle représentant une facture scolaire.
 *
 * Gère la facturation des frais de scolarité pour les élèves d'un
 * établissement. Chaque facture est liée à un client (établissement)
 * et récapitule les frais par type, avec gestion des remises,
 * des échéances et du suivi des paiements.
 *
 * @property int $id
 * @property int|null $client_id Identifiant du client (établissement scolaire)
 * @property string $numero_facture Numéro de la facture
 * @property string $annee_scolaire Année scolaire concernée
 * @property string $eleve_nom Nom de l'élève
 * @property string $eleve_prenom Prénom de l'élève
 * @property string $classe Classe de l'élève
 * @property string $matricule Matricule de l'élève
 * @property string $type_frais Type de frais (scolarite, inscription, cantine, etc.)
 * @property string $periode Période concernée
 * @property float $montant_du Montant dû
 * @property float $remise Remise accordée
 * @property float $montant_net Montant net après remise
 * @property float $montant_paye Montant payé
 * @property float $solde Solde restant
 * @property string $statut Statut (payee, impayee, partielle)
 * @property string|null $date_echeance Date d'échéance
 * @property string|null $date_paiement Date de paiement
 * @property string|null $mode_paiement Mode de paiement
 * @property string|null $notes Notes
 * @property int|null $created_by Identifiant de l'utilisateur créateur
 *
 * @property-read Client|null $client Client (établissement) associé
 *
 * @table accounting_scolaire_factures
 */
class AccountingScolaireFacture extends Model
{
    use SoftDeletes;
    protected $table = 'accounting_scolaire_factures';
    protected $fillable = [
        'client_id','numero_facture','annee_scolaire','eleve_nom','eleve_prenom',
        'classe','matricule','type_frais','periode','montant_du','remise',
        'montant_net','montant_paye','solde','statut','date_echeance',
        'date_paiement','mode_paiement','notes','created_by',
    ];
    protected $casts = [
        'date_echeance' => 'date', 'date_paiement' => 'date',
        'montant_du' => 'decimal:2', 'remise' => 'decimal:2',
        'montant_net' => 'decimal:2', 'montant_paye' => 'decimal:2', 'solde' => 'decimal:2',
    ];
    public function client() { return $this->belongsTo(Client::class); }
    public function scopeForClient($q, $cid) { return $q->where('client_id', $cid); }
}
