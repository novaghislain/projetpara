<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DaeDocumentDossier extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'dae_document_dossiers';

    protected $fillable = [
        'client_id',
        'nom',
        'description',
        'statut'
    ];

}
