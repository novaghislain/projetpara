<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Dae\DaeOfficeSupply;
use App\Models\Dae\DaeOfficeSupplyRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class OfficeSupplyController extends Controller
{
    public function index(Request $request)
    {
        $supplies = DaeOfficeSupply::where('client_id', Auth::user()->client_id)
            ->latest()
            ->paginate(15);
            
        $requests = DaeOfficeSupplyRequest::where('client_id', Auth::user()->client_id)
            ->with(['supply', 'user'])
            ->latest()
            ->paginate(15);

        if ($request->wantsJson()) {
            return response()->json([
                'supplies' => $supplies,
                'requests' => $requests,
            ]);
        }

        return Inertia::render('Company/Settings/OfficeSupplies', [
            'supplies' => $supplies,
            'requests' => $requests,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'stock_quantity' => 'required|integer|min:0',
            'min_threshold' => 'required|integer|min:0',
        ]);

        $data['client_id'] = Auth::user()->client_id;
        $supply = DaeOfficeSupply::create($data);

        return response()->json(['message' => 'Fourniture ajoutée avec succès', 'supply' => $supply]);
    }

    public function requestSupply(Request $request)
    {
        $data = $request->validate([
            'dae_office_supply_id' => 'required|exists:dae_office_supplies,id',
            'quantity_requested' => 'required|integer|min:1',
            'reason' => 'nullable|string',
        ]);

        $data['client_id'] = Auth::user()->client_id;
        $data['user_id'] = Auth::id();
        $data['status'] = 'en_attente';
        
        $supplyRequest = DaeOfficeSupplyRequest::create($data);

        return response()->json(['message' => 'Demande de fourniture envoyée', 'request' => $supplyRequest]);
    }

    public function destroy(DaeOfficeSupply $officeSupply)
    {
        if ($officeSupply->client_id !== Auth::user()->client_id) {
            abort(403);
        }
        $officeSupply->delete();
        return response()->json(['message' => 'Fourniture supprimée']);
    }
}
