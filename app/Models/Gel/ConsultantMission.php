<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsultantMission extends Model
{
    use HasFactory;

    protected $fillable = [
        'entreprise_id', 'created_by', 'consultant_id', 'title', 'description',
        'specialty', 'status', 'start_date', 'end_date'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function entreprise()
    {
        return $this->belongsTo(\App\Models\Gel\Entreprise::class, 'entreprise_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function consultant()
    {
        return $this->belongsTo(\App\Models\User::class, 'consultant_id');
    }

    public function deliverables()
    {
        return $this->hasMany(ConsultantDeliverable::class, 'mission_id');
    }

    public function audits()
    {
        return $this->hasMany(ConsultantAudit::class, 'mission_id');
    }

    public function isExpired()
    {
        return $this->end_date && now()->startOfDay()->greaterThan($this->end_date);
    }
}
