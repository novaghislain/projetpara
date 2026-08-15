<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DaeDocument extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'dae_documents';

    protected $fillable = [
        'client_id',
        'user_id',
        'nom',
        'type',
        'categorie',
        'fichier_path',
        'taille',
        'mime_type',
        'statut'
    ];

}
