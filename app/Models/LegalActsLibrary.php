<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegalActsLibrary extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'legal_acts_library';

    protected $fillable = [
        'titre',
        'categorie',
        'contenu',
        'fichier_path',
        'actif'
    ];

}
