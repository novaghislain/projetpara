<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegalLitigation extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'legal_litigations';

    protected $fillable = [
        'client_id',
        'intitule',
        'type',
        'partie_adverse',
        'statut',
        'date_ouverture'
    ];

}
