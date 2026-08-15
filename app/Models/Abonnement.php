<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Abonnement extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'abonnements';

    protected $fillable = [
        'entreprise_id',
        'payeur_utilisateur_id',
        'plan',
        'services_inclus',
        'date_debut',
        'date_fin',
        'statut'
    ];

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class, 'entreprise_id', 'id');
    }

    public function payeurUtilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'payeur_utilisateur_id', 'id');
    }

}
