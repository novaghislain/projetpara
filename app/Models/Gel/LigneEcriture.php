<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;

class LigneEcriture extends Model
{
    protected $table = 'gel_lignes_ecriture';

    protected $fillable = [
        'ecriture_id',
        'compte_id',
        'sens',
        'montant',
        'libelle_ligne',
        'tiers_id',
    ];

    protected $casts = [
        'montant' => 'decimal:0',
    ];

    public function ecriture()
    {
        return $this->belongsTo(EcritureComptable::class, 'ecriture_id');
    }

    public function compte()
    {
        return $this->belongsTo(CompteComptable::class, 'compte_id');
    }

    public function tiers()
    {
        return $this->belongsTo(Client::class, 'tiers_id');
    }

    public function scopeDebit($query)
    {
        return $query->where('sens', 'debit');
    }

    public function scopeCredit($query)
    {
        return $query->where('sens', 'credit');
    }
}
