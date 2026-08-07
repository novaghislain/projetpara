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
        $search = $request->get('search');
        
        $query = ItDevRequest::with(['client', 'author'])->latest();
        
        if ($status) {
            $query->where('status', $status);
        }
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('client', function($qc) use ($search) {
                      $qc->where('company_name', 'like', "%{$search}%");
                  });
            });
        }
        
        $requests = $query->paginate(20);
        $requests->appends(['status' => $status, 'search' => $search]);

        return view('gel-informaticien.dev-requests.index', compact('requests', 'status'));
    }

    /**
     * Formulaire de création de demande dev
     */
    public function create()
    {
        $clients = \App\Models\Client::orderBy('company_name')->get();
        return view('gel-informaticien.dev-requests.create', compact('clients'));
    }

    /**
     * Enregistrer une nouvelle demande
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'client_id' => 'nullable|exists:clients,id',
            'description' => 'required|string',
        ]);

        ItDevRequest::create([
            'subject' => $validated['subject'],
            'client_id' => $validated['client_id'],
            'description' => $validated['description'],
            'author_id' => auth()->id(),
            'status' => 'recue',
        ]);

        return redirect()->route('gel-informaticien.dev-requests.index')->with('success', 'Demande de développement créée avec succès.');
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
