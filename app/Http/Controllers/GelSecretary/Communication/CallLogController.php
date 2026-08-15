<?php

namespace App\Http\Controllers\GelSecretary\Communication;

use App\Http\Controllers\Controller;
use App\Models\Gel\Client;
use App\Models\ClientCallLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CallLogController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $query = ClientCallLog::with(['client', 'user']);
            
        $activeClientId = session('active_client_id') ?? $user->active_client_id ?? $user->client_id;
        if ($activeClientId) {
            $query->where('client_id', $activeClientId);
        }

        $calls = $query->orderBy('called_at', 'desc')->get();
        $clients = Client::orderBy('nom_entreprise')->get();
        $activeClient = $activeClientId ? Client::find($activeClientId) : $clients->first();

        return view('gel-secretary.calls.index', compact('calls', 'clients', 'activeClient'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'direction' => 'required|in:entrant,sortant',
            'contact_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'statut' => 'required|string',
            'notes' => 'nullable|string',
            'called_at' => 'required|date',
            'a_rappeler' => 'nullable|boolean',
            'date_rappel' => 'nullable|date',
        ]);

        $validated['user_id'] = Auth::id();
        
        $call = ClientCallLog::create($validated);

        // Si à rappeler est coché, on crée un RDV / Tâche automatiquement dans l'agenda
        if ($request->has('a_rappeler') && $request->filled('date_rappel')) {
            \App\Models\Dae\DaeAgendaEvent::create([
                'client_id' => $validated['client_id'],
                'created_by' => Auth::id(),
                'type' => 'appel',
                'title' => 'Rappeler ' . ($validated['contact_name'] ?? 'le client'),
                'description' => 'Suite à l\'appel du ' . now()->format('d/m/Y') . " : \n" . $validated['notes'],
                'start_at' => \Carbon\Carbon::parse($validated['date_rappel'])->format('Y-m-d H:i:s'),
                'end_at' => \Carbon\Carbon::parse($validated['date_rappel'])->addMinutes(15)->format('Y-m-d H:i:s'),
                'statut' => 'confirme'
            ]);
        }

        return redirect()->back()->with('success', 'Appel consigné avec succès.');
    }

    public function analyzeIa(Request $request, \App\Services\AnthropicService $anthropic)
    {
        $request->validate([
            'text_content' => 'required|string',
        ]);

        $prompt = "Voici les notes rapides d'un appel téléphonique.
Extrais les informations suivantes au format JSON strictement :
- contact_name: nom de la personne
- phone: numéro de téléphone si mentionné (sinon null)
- notes: un bref résumé pro de l'appel
- a_rappeler: true ou false (si on doit le rappeler)
- date_rappel: date/heure suggérée au format YYYY-MM-DD HH:MM (si mentionné, sinon null)

Notes de l'appel :
" . $request->text_content;

        $json = $anthropic->generateJson($prompt);

        \App\Services\AuditLogService::log('IA ACTION', Auth::user(), null, ['action' => 'Synthèse Appel IA']);

        return response()->json($json ?? [
            'contact_name' => '',
            'phone' => '',
            'notes' => '',
            'a_rappeler' => false,
            'date_rappel' => null
        ]);
    }
}


