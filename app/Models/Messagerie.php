<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Messagerie extends Model
{
    use HasFactory;
    protected $table = 'messagerie';

    protected $fillable = [
        'client_id',
        'sender_id',
        'sender_type',
        'contenu',
        'est_lu'
    ];

}
