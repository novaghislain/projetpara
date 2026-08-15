<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Model;

class VehicleMaintenance extends Model
{
    protected $fillable = [
        'vehicle_id',
        'date',
        'type', // oil_change, tire_replacement, repair, inspection
        'description',
        'cost',
        'mileage_at_maintenance'
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
