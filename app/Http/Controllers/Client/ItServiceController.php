<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Gel\ItMission;
use App\Models\Gel\ItEquipmentOrder;
use App\Models\Gel\ItIntervention;
use Illuminate\Support\Str;

class ItServiceController extends Controller
{
    public function index()
    {
        $client = auth()->user()->client;
        
        // If user is not tied to a client (shouldn't happen on client portal but safe check)
        if (!$client) {
            abort(403, 'Accès réservé aux entreprises clientes.');
        }

        $missions = ItMission::where('client_id', $client->id)->latest()->get();
        $orders = ItEquipmentOrder::where('client_id', $client->id)->latest()->get();
        $interventions = ItIntervention::whereHas('mission', function($q) use ($client) {
            $q->where('client_id', $client->id);
        })->orderBy('scheduled_at', 'asc')->take(10)->get();

        return view('client.it-services.index', compact('missions', 'orders', 'interventions'));
    }

    public function subscribeForm()
    {
        return view('client.it-services.subscribe');
    }

    public function storeMission(Request $request)
    {
        $request->validate([
            'type' => 'required|string|in:securite,maintenance,developpement',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'volume' => 'nullable|string',
        ]);

        $client = auth()->user()->client;

        ItMission::create([
            'client_id' => $client->id,
            'type' => $request->type,
            'subject' => $request->subject,
            'description' => $request->description,
            'volume' => $request->volume,
            'status' => 'en_attente',
        ]);

        return redirect()->route('client.it.index')->with('success', 'Votre demande de mission IT a bien été enregistrée. Un responsable va vous être affecté.');
    }

    public function orderForm()
    {
        return view('client.it-services.order');
    }

    public function storeOrder(Request $request)
    {
        $request->validate([
            'items' => 'required|string', // Simple text area for requested items for now
        ]);

        $client = auth()->user()->client;
        
        $orderNumber = 'CMD-IT-' . strtoupper(Str::random(6)) . '-' . date('my');

        ItEquipmentOrder::create([
            'client_id' => $client->id,
            'order_number' => $orderNumber,
            'items' => explode("\n", $request->items),
            'status' => 'recue',
        ]);

        return redirect()->route('client.it.index')->with('success', 'Votre commande de matériel a été transmise. Nous vous enverrons un devis rapidement.');
    }
}
