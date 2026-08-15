<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pv extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'client_id',
        'event_id',
        'titre',
        'contenu',
        'statut',
        'cree_par',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function creePar()
    {
        return $this->belongsTo(User::class, 'cree_par');
    }
}
