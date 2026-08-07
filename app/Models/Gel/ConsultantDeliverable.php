<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsultantDeliverable extends Model
{
    use HasFactory;

    protected $fillable = [
        'mission_id', 'consultant_id', 'file_path', 'original_name', 'notes'
    ];

    public function mission()
    {
        return $this->belongsTo(ConsultantMission::class, 'mission_id');
    }

    public function consultant()
    {
        return $this->belongsTo(\App\Models\User::class, 'consultant_id');
    }
}
