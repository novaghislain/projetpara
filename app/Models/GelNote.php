<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GelNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'user_id',
        'titre',
        'contenu',
        'statut'
    ];

    public function client()
    {
        return $this->belongsTo(\App\Models\Gel\Client::class, 'client_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
