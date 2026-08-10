<?php

namespace App\Http\Controllers\GelDirection;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ValidationController extends Controller
{
    public function index()
    {
        // Fake data for validations pending direction approval
        $validations = [
            (object)[
                'id' => 1,
                'type' => 'Demande de Congés',
                'requester' => 'Sarah (Comptable)',
                'details' => 'Du 15/07 au 30/07',
                'date' => now()->subDays(1)->format('d/m/Y'),
                'status' => 'pending'
            ],
            (object)[
                'id' => 2,
                'type' => 'Validation Recrutement',
                'requester' => 'Service RH',
                'details' => 'Embauche d\'un nouveau consultant',
                'date' => now()->subDays(2)->format('d/m/Y'),
                'status' => 'pending'
            ],
            (object)[
                'id' => 3,
                'type' => 'Contrat Client Exceptionnel',
                'requester' => 'Commercial',
                'details' => 'Remise de 20% accordée',
                'date' => now()->format('d/m/Y'),
                'status' => 'pending'
            ],
        ];

        return view('gel-direction.validations.index', compact('validations'));
    }
    
    public function approve(Request $request, $id)
    {
        return back()->with('success', 'La demande a été approuvée avec succès.');
    }
    
    public function reject(Request $request, $id)
    {
        return back()->with('error', 'La demande a été rejetée.');
    }
}
