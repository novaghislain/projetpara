<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupportTicket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'entreprise_id',
        'user_id',
        'subject',
        'message',
        'type',
        'status',
    ];

    public function entreprise()
    {
        return $this->belongsTo(\App\Models\Gel\Entreprise::class, 'entreprise_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
