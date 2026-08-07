<?php

namespace App\Http\Controllers\GelInformaticien;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Gel\ItEquipmentOrder;
use Illuminate\Support\Facades\Auth;

class EquipmentOrderController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status');
        
        $query = ItEquipmentOrder::where('informaticien_id', Auth::id())->with('client');

        if ($status) {
            $query->where('status', $status);
        }

        $orders = $query->latest()->paginate(20);
        $orders->appends(['status' => $status]);

        return view('gel-informaticien.equipment.index', compact('orders', 'status'));
    }

    public function updateStatus(Request $request, $orderId)
    {
        $request->validate(['status' => 'required|in:recue,devis_envoye,valide,approvisionnement,expedie,livre']);
        
        $order = ItEquipmentOrder::where('informaticien_id', Auth::id())->findOrFail($orderId);
        $order->update(['status' => $request->status]);

        // Optionnel : Envoyer une notification au client via un événement (ex: OrderStatusUpdated)

        return back()->with('success', 'Statut de la commande mis à jour.');
    }
}
