<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FixedAsset extends Model
{
    use SoftDeletes;

    protected $table = 'gel_fixed_assets';

    protected $fillable = [
        'cabinet_id', 'client_id', 'compte_id', 'nom', 'description',
        'date_acquisition', 'cout_acquisition', 'valeur_residuelle', 'duree_vie',
        'methode_amort', 'taux_amort', 'amort_cumule', 'vnc',
        'statut', 'date_cession', 'prix_cession', 'plus_value',
    ];

    protected $casts = [
        'date_acquisition' => 'date',
        'date_cession' => 'date',
        'cout_acquisition' => 'decimal:2',
        'valeur_residuelle' => 'decimal:2',
        'amort_cumule' => 'decimal:2',
        'vnc' => 'decimal:2',
        'prix_cession' => 'decimal:2',
        'plus_value' => 'decimal:2',
    ];

    // ─── Relations ───
    public function cabinet() { return $this->belongsTo(Cabinet::class); }
    public function client() { return $this->belongsTo(Client::class); }
    public function compte() { return $this->belongsTo(CompteComptable::class, 'compte_id'); }

    // ─── Scopes ───
    public function scopeActif($q) { return $q->where('statut', 'actif'); }
    public function scopeCede($q) { return $q->where('statut', 'cede'); }

    // ─── Calculs ───
    public function calculerAmortAnnuel(): float
    {
        $base = $this->cout_acquisition - $this->valeur_residuelle;
        if ($this->methode_amort === 'lineaire') {
            return $base / max($this->duree_vie, 1);
        }
        // degressif
        $taux = $this->taux_amort ?: (100 / max($this->duree_vie, 1));
        return ($this->cout_acquisition - $this->amort_cumule) * ($taux / 100);
    }

    public function calculerVnc(): float
    {
        return $this->cout_acquisition - $this->amort_cumule;
    }

    public function genererEcritureAmort(): array
    {
        $montant = $this->calculerAmortAnnuel() / 12; // mensuel
        return [
            'debit' => ['compte_id' => 66, 'montant' => $montant],
            'credit' => ['compte_id' => 28, 'montant' => $montant],
        ];
    }
}
