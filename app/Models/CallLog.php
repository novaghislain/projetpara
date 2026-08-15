<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallLog extends Model
{
    use HasFactory;
    protected $table = 'call_logs';

    protected $fillable = [
        'client_id',
        'user_id',
        'contact_nom',
        'contact_numero',
        'sens',
        'duree_secondes',
        'notes'
    ];

}
