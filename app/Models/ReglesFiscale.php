<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReglesFiscale extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'regles_fiscales';

    protected $fillable = [
        'code_pays',
        'type_impot',
        'taux',
        'conditions',
        'date_debut_validite',
        'date_fin_validite',
        'source_reglementaire',
        'version',
        'statut',
        'cree_par'
    ];

    public function creePar()
    {
        return $this->belongsTo(Utilisateur::class, 'cree_par', 'id');
    }

}
