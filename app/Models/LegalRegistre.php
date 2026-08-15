<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegalRegistre extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'legal_registres';

    protected $fillable = [
        'client_id',
        'type',
        'reference',
        'date_ouverture',
        'statut'
    ];

}
