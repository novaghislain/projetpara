<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsultantInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'mission_id', 'email', 'token', 'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function mission()
    {
        return $this->belongsTo(ConsultantMission::class, 'mission_id');
    }
}
