<?php

namespace App\Models\Compta;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Client;

class Immobilisation extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'code', 'designation', 'categorie', 'date_acquisition', 
        'date_mise_service', 'valeur_acquisition', 'duree_amortissement', 
        'methode_amortissement', 'amortissement_cumule', 'valeur_nette_comptable', 
        'compte_immobilisation_id', 'compte_amortissement_id', 'compte_dotation_id', 
        'is_sortie', 'date_sortie'
    ];

    protected $casts = [
        'date_acquisition' => 'date',
        'date_mise_service' => 'date',
        'date_sortie' => 'date',
        'is_sortie' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function compteImmobilisation()
    {
        return $this->belongsTo(Compte::class, 'compte_immobilisation_id');
    }
}
