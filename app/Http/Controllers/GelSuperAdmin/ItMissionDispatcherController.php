<?php

namespace App\Http\Controllers\GelSuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Gel\ItMission;
use App\Models\Gel\ItEquipmentOrder;
use App\Models\User;

class ItMissionDispatcherController extends Controller
{
    public function index()
    {
        $unassignedMissions = ItMission::where('status', 'en_attente')
                                       ->doesntHave('informaticiens')
                                       ->latest()
                                       ->get();
        
        $unassignedOrders = ItEquipmentOrder::where('status', 'recue')
                                            ->whereNull('informaticien_id')
                                            ->latest()
                                            ->get();
                                            
        $informaticiens = User::role('informaticien')->get();

        return view('gel-super-admin.it-dispatcher.index', compact('unassignedMissions', 'unassignedOrders', 'informaticiens'));
    }

    public function assignMission(Request $request, $id)
    {
        $request->validate([
            'informaticien_ids' => 'required|array',
            'informaticien_ids.*' => 'exists:users,id',
        ]);

        $mission = ItMission::findOrFail($id);
        $mission->informaticiens()->sync($request->informaticien_ids);
        $mission->update(['status' => 'en_cours']);

        return back()->with('success', 'La mission a été affectée.');
    }

    public function assignOrder(Request $request, $id)
    {
        $request->validate([
            'informaticien_id' => 'required|exists:users,id',
        ]);

        $order = ItEquipmentOrder::findOrFail($id);
        $order->update([
            'informaticien_id' => $request->informaticien_id,
        ]);

        return back()->with('success', 'La commande a été affectée pour traitement.');
    }
}
