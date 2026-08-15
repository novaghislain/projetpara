<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RhAlert extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'rh_alerts';

    protected $fillable = [
        'client_id',
        'type',
        'message',
        'statut',
        'echeance_at'
    ];

}
