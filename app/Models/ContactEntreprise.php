<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactEntreprise extends Model
{
    use HasFactory;
    protected $table = 'contact_entreprise';

    protected $fillable = [
        'client_id',
        'portal_contact_id',
        'is_active'
    ];

}
