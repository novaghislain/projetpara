<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailModele extends Model
{
    protected $table = 'email_modeles';

    protected $fillable = [
        'nom',
        'sujet',
        'corps_html',
        'type',
        'actif',
    ];
}
