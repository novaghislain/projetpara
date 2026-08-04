<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'email',
        'role',
        'token',
        'status',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
