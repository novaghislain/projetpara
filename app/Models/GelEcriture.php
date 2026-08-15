<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GelEcriture extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'gel_ecritures';

    protected $fillable = [
        'cabinet_id',
        'client_id',
        'journal_id',
        'exercice_id',
        'date_ecriture',
        'numero_piece',
        'libelle',
        'valide',
        'valide_at',
        'valide_par',
        'reference',
        'notes',
        'source_type',
        'source_id'
    ];

    public function gelLignesEcriture()
    {
        return $this->hasMany(GelLignesEcriture::class, 'ecriture_id', 'id');
    }

}
