<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ItIntervention extends Model
{
    use HasFactory;

    protected $fillable = [
        'it_mission_id',
        'informaticien_id',
        'description',
        'scheduled_at',
        'completed_at',
        'status',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function mission()
    {
        return $this->belongsTo(ItMission::class, 'it_mission_id');
    }

    public function informaticien()
    {
        return $this->belongsTo(\App\Models\User::class, 'informaticien_id');
    }
}
