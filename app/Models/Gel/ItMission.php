<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ItMission extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'type',
        'subject',
        'description',
        'volume',
        'status',
    ];

    public function client()
    {
        return $this->belongsTo(\App\Models\Client::class);
    }

    public function informaticiens()
    {
        return $this->belongsToMany(\App\Models\User::class, 'it_mission_user', 'it_mission_id', 'user_id');
    }

    public function interventions()
    {
        return $this->hasMany(ItIntervention::class);
    }
}
