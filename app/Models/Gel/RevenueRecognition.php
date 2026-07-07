<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
