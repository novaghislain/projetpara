<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle RevenueRecognition (Reconnaissance de revenus).
 *
 * Gère l'étalement et la reconnaissance des revenus différés dans le temps.
 * Permet de suivre le montant total, le montant différé, le montant déjà reconnu,
 * ainsi que le taux de reconnaissance pour un produit ou service donné.
 * Table associée : `gel_revenue_recognition`.
 * Supporte la suppression douce (SoftDeletes).
 *
 * @property int $id
 * @property int $cabinet_id ID du cabinet
 * @property int $client_id ID du client
 * @property int $compte_produit_id ID du compte comptable de produit
 * @property string $libelle Libellé du revenu
 * @property string $modele Modèle de reconnaissance
 * @property float $montant_total Montant total du contrat
 * @property float $montant_differe Montant différé
 * @property float $montant_reconnu Montant déjà reconnu
 * @property \Carbon\Carbon $date_debut Date de début de reconnaissance
 * @property \Carbon\Carbon $date_fin Date de fin de reconnaissance
 * @property string $frequence Fréquence de reconnaissance (mensuelle, trimestrielle, etc.)
 * @property string $statut Statut (en_cours, termine)
 *
 * @property-read \App\Models\Gel\Cabinet $cabinet Cabinet associé
 * @property-read \App\Models\Gel\Client $client Client associé
 * @property-read \App\Models\Gel\CompteComptable|null $compteProduit Compte de produit comptable
 */
class RevenueRecognition extends Model
{
    use SoftDeletes;

    protected $table = 'gel_revenue_recognition';

    protected $fillable = [
        'cabinet_id', 'client_id', 'compte_produit_id', 'libelle', 'modele',
        'montant_total', 'montant_differe', 'montant_reconnu',
        'date_debut', 'date_fin', 'frequence', 'statut',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'montant_total' => 'decimal:2',
        'montant_differe' => 'decimal:2',
        'montant_reconnu' => 'decimal:2',
    ];

    // ─── Relations ───
    public function cabinet() { return $this->belongsTo(Cabinet::class); }
    public function client() { return $this->belongsTo(Client::class); }
    public function compteProduit() { return $this->belongsTo(CompteComptable::class, 'compte_produit_id'); }

    // ─── Scopes ───
    public function scopeEnCours($q) { return $q->where('statut', 'en_cours'); }
    public function scopeTermine($q) { return $q->where('statut', 'termine'); }

    // ─── Helpers ───
    public function montantRestant(): float
    {
        return $this->montant_total - $this->montant_reconnu;
    }

    public function tauxReconnaissance(): float
    {
        if ($this->montant_total <= 0) return 0;
        return round(($this->montant_reconnu / $this->montant_total) * 100, 2);
    }
}
