<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Facture extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'factures';

    protected $fillable = [
        'entreprise_id',
        'client_id',
        'numero',
        'date_emission',
        'date_echeance',
        'montant_ht',
        'montant_tva',
        'montant_ttc',
        'statut'
    ];

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function lignes()
    {
        return $this->hasMany(LigneFacture::class);
    }
}
