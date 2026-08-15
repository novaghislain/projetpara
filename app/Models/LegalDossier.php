<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegalDossier extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'legal_dossiers';

    protected $fillable = [
        'client_id',
        'intitule',
        'type',
        'statut',
        'description'
    ];

}
