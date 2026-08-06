<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItDevRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'author_id',
        'subject',
        'description',
        'status',
        'devis_url',
    ];

    public function client()
    {
        return $this->belongsTo(\App\Models\Client::class);
    }

    public function author()
    {
        return $this->belongsTo(\App\Models\User::class, 'author_id');
    }
}
