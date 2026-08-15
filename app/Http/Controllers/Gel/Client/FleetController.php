<?php

namespace App\Http\Controllers\Gel\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Fleet\Vehicle;
use App\Models\Fleet\VehicleMaintenance;

class FleetController extends Controller
{
    public function addVehicle(Request $request)
    {
        $request->validate([
            'client_id' => 'required|uuid',
            'registration_number' => 'required|string|unique:vehicles',
            'brand' => 'required|string',
            'model' => 'required|string',
            'year' => 'required|integer',
            'current_mileage' => 'required|integer|min:0'
        ]);

        $vehicle = Vehicle::create($request->all());

        return response()->json([
            'status' => 'success',
            'data' => $vehicle,
            'message' => 'Véhicule ajouté à la flotte.'
        ]);
    }

    public function logMaintenance(Request $request, $vehicleId)
    {
        $vehicle = Vehicle::findOrFail($vehicleId);

        $request->validate([
            'date' => 'required|date',
            'type' => 'required|string',
            'cost' => 'required|numeric|min:0',
            'mileage_at_maintenance' => 'required|integer|min:0'
        ]);

        $maintenance = VehicleMaintenance::create(array_merge($request->all(), [
            'vehicle_id' => $vehicleId
        ]));

        // Update vehicle mileage if this maintenance has higher mileage
        if ($request->mileage_at_maintenance > $vehicle->current_mileage) {
            $vehicle->update(['current_mileage' => $request->mileage_at_maintenance]);
        }

        return response()->json([
            'status' => 'success',
            'data' => $maintenance,
            'message' => 'Maintenance enregistrée.'
        ]);
    }
}
