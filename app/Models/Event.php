<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'client_id',
        'titre',
        'description',
        'date_debut',
        'date_fin',
        'lieu',
        'contact_id',
        'cree_par',
    ];

    protected $casts = [
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function pvs()
    {
        return $this->hasMany(Pv::class, 'event_id');
    }

    public function creePar()
    {
        return $this->belongsTo(User::class, 'cree_par');
    }
}
