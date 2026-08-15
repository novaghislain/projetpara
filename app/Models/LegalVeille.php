<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegalVeille extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'legal_veille';

    protected $fillable = [
        'titre',
        'source',
        'contenu',
        'date_publication',
        'categorie'
    ];

}
