<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = [
        'client_id',
        'registration_number', // Immatriculation
        'brand',
        'model',
        'year',
        'status', // active, maintenance, retired
        'current_mileage' // Kilométrage
    ];

    public function maintenances()
    {
        return $this->hasMany(VehicleMaintenance::class);
    }
}
