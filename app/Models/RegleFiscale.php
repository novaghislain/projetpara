<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RegleFiscale extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

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
        'cree_par',
    ];

    protected $casts = [
        'taux' => 'decimal:4',
        'conditions' => 'array',
        'date_debut_validite' => 'date',
        'date_fin_validite' => 'date',
    ];

    public function createur()
    {
        return $this->belongsTo(User::class, 'cree_par');
    }
}
