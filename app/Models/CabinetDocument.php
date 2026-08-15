<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CabinetDocument extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'cabinet_documents';

    protected $fillable = [
        'cabinet_id',
        'nom',
        'categorie',
        'fichier_path'
    ];

}
