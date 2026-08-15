<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransmissionsInterne extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'transmissions_internes';

    protected $fillable = [
        'from_user_id',
        'to_user_id',
        'client_id',
        'objet',
        'message',
        'est_lu',
        'statut'
    ];

}
