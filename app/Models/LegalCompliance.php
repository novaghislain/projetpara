<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegalCompliance extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'legal_compliance';

    protected $fillable = [
        'client_id',
        'domaine',
        'obligation',
        'statut',
        'echeance'
    ];

}
