<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsultantAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'mission_id', 'user_id', 'action', 'ip_address', 'details'
    ];

    public function mission()
    {
        return $this->belongsTo(ConsultantMission::class, 'mission_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
