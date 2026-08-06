<?php

namespace App\Http\Controllers\GelInformaticien;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Gel\ItDevRequest;

class DevRequestController extends Controller
{
    /**
     * Affiche la liste des demandes de développement
     */
    public function index(Request $request)
    {
        $status = $request->get('status');
        
        $query = ItDevRequest::with(['client', 'author'])->latest();
        
        if ($status) {
            $query->where('status', $status);
        }
        
        $requests = $query->paginate(20);

        return view('gel-informaticien.dev-requests.index', compact('requests', 'status'));
    }

    /**
     * Affiche le détail d'une demande de développement
     */
    public function show($id)
    {
        $devRequest = ItDevRequest::with(['client', 'author'])->findOrFail($id);
        
        return view('gel-informaticien.dev-requests.show', compact('devRequest'));
    }

    /**
     * Met à jour le statut et l'URL du devis
     */
    public function update(Request $request, $id)
    {
        $devRequest = ItDevRequest::findOrFail($id);
        
        $validated = $request->validate([
            'status' => 'nullable|string|in:recue,devis_en_cours,en_developpement,livre',
            'devis_url' => 'nullable|url',
        ]);
        
        $devRequest->update($validated);
        
        return back()->with('success', 'Demande de développement mise à jour.');
    }
}
