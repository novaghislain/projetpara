<?php

namespace App\Models\Crm;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $fillable = [
        'client_id', // Le tenant / l'entreprise ciblée
        'author_name',
        'author_company',
        'author_position',
        'rating', // sur 5
        'content',
        'status', // pending, approved, rejected
        'is_public'
    ];

    protected $casts = [
        'is_public' => 'boolean'
    ];

    public function client()
    {
        return $this->belongsTo(\App\Models\Client::class);
    }
}
