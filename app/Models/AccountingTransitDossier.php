<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle représentant un dossier de transit douanier.
 *
 * Gère le suivi des opérations de transit et de dédouanement
 * de marchandises. Chaque dossier est lié à un client (transitaire)
 * et contient les informations sur la marchandise, les frais
 * (fret, droits de douane, TVA douane, frais accessoires)
 * et le suivi des paiements.
 *
 * @property int $id
 * @property int|null $client_id Identifiant du client (transitaire)
 * @property string $reference_dossier Référence du dossier
 * @property string $type_transit Type de transit (import, export, etc.)
 * @property string $fournisseur_nom Nom du fournisseur
 * @property string $client_nom Nom du client propriétaire
 * @property string $marchandise Description de la marchandise
 * @property float $valeur_marchandise Valeur de la marchandise
 * @property float $fret_ht Fret hors taxes
 * @property float $droits_douane Droits de douane
 * @property float $tva_douane TVA douane
 * @property float $frais_accessoires Frais accessoires
 * @property float $total_facture Total de la facture
 * @property float $montant_paye Montant payé
 * @property float $solde Solde restant
 * @property string $statut Statut (en_cours, cloture, annule)
 * @property string|null $date_ouverture Date d'ouverture du dossier
 * @property string|null $date_cloture Date de clôture
 * @property string|null $douane_bureau Bureau de douane
 * @property string|null $numero_declaration Numéro de déclaration en douane
 * @property string|null $notes Notes
 * @property int|null $created_by Identifiant de l'utilisateur créateur
 *
 * @property-read Client|null $client Client (transitaire) associé
 *
 * @table accounting_transit_dossiers
 */
class AccountingTransitDossier extends Model
{
    use SoftDeletes;
    protected $table = 'accounting_transit_dossiers';
    protected $fillable = [
        'client_id','reference_dossier','type_transit','fournisseur_nom','client_nom',
        'marchandise','valeur_marchandise','fret_ht','droits_douane','tva_douane',
        'frais_accessoires','total_facture','montant_paye','solde','statut',
        'date_ouverture','date_cloture','douane_bureau','numero_declaration','notes','created_by',
    ];
    protected $casts = [
        'date_ouverture' => 'date', 'date_cloture' => 'date',
        'valeur_marchandise' => 'decimal:2', 'fret_ht' => 'decimal:2',
        'droits_douane' => 'decimal:2', 'tva_douane' => 'decimal:2',
        'frais_accessoires' => 'decimal:2', 'total_facture' => 'decimal:2',
        'montant_paye' => 'decimal:2', 'solde' => 'decimal:2',
    ];
    public function client() { return $this->belongsTo(Client::class); }
    public function scopeForClient($q, $cid) { return $q->where('client_id', $cid); }
}
