<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'client_id',
        'type',
        'nom',
        'email',
        'telephone',
        'adresse',
        'ville',
        'code_postal',
        'pays',
        'ifu',
        'site_web',
        'devise_facturation',
        'delai_paiement',
        'methode_paiement',
        'iban',
        'swift',
    ];

    public function events()
    {
        return $this->hasMany(Event::class, 'contact_id');
    }
}
