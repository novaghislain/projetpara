<?php

namespace App\Http\Controllers\GelSecretary\Tasks;

use App\Http\Controllers\Controller;
use App\Models\Gel\Client;
use App\Models\Dae\DaeMeetingMinute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\AnthropicService;
use App\Services\AuditLogService;

class MeetingMinuteController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = DaeMeetingMinute::with(['client', 'redacteur']);
            
        $activeClientId = $request->query('client_id') ?? session('active_client_id') ?? $user->active_client_id ?? $user->client_id;
        if ($activeClientId) {
            $query->where('client_id', $activeClientId);
        }

        $pvs = $query->orderBy('date_reunion', 'desc')->get();
        $clients = Client::orderBy('nom_entreprise')->get();
        $activeClient = $activeClientId ? Client::find($activeClientId) : $clients->first();

        return view('gel-secretary.pv.index', compact('pvs', 'clients', 'activeClient'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'titre' => 'required|string|max:255',
            'date_reunion' => 'required|date',
            'heure_debut' => 'nullable|date_format:H:i',
            'heure_fin' => 'nullable|date_format:H:i',
            'lieu' => 'nullable|string|max:255',
            'participants' => 'nullable|string', // We'll save it as array but UI sends string, or we convert
            'ordre_du_jour' => 'nullable|string',
            'decisions' => 'nullable|string',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['redige_par'] = Auth::id();
        $validated['statut'] = 'brouillon';
        
        // Convert multiline text to array for JSON columns
        if (!empty($validated['participants'])) {
            $validated['participants'] = array_map('trim', explode("\n", $validated['participants']));
        }
        if (!empty($validated['ordre_du_jour'])) {
            $validated['ordre_du_jour'] = array_map('trim', explode("\n", $validated['ordre_du_jour']));
        }
        
        // Handle decisions — new format is JSON from the builder, old format is plain text
        if (!empty($validated['decisions'])) {
            $decoded = json_decode($validated['decisions'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                // New structured format: [{action, responsable, echeance}]
                $validated['decisions'] = $decoded;
            } else {
                // Fallback: plain text, convert to simple array
                $validated['decisions'] = array_filter(array_map('trim', explode("\n", $validated['decisions'])));
            }
        }

        $pv = DaeMeetingMinute::create($validated);

        return redirect()->back()->with('success', 'Procès-verbal créé avec succès en tant que brouillon.');
    }

    public function show($id)
    {
        $pv = DaeMeetingMinute::with('client')->findOrFail($id);
        return response()->json($pv);
    }

    public function update(Request $request, $id)
    {
        $pv = DaeMeetingMinute::findOrFail($id);
        
        $validated = $request->validate([
            'statut' => 'required|in:brouillon,en_attente_signature,signe_archive',
        ]);

        if ($validated['statut'] === 'signe_archive' && $pv->statut !== 'signe_archive') {
            $validated['approuve_at'] = now();
            $validated['approuve_par'] = Auth::id();
            
            // Création automatique de tâches pour chaque décision
            if (is_array($pv->decisions)) {
                foreach ($pv->decisions as $decision) {
                    // Support both old string format and new [{action, responsable, echeance}] format
                    if (is_array($decision)) {
                        $actionText = $decision['action'] ?? '';
                        $responsable = $decision['responsable'] ?? null;
                        $echeance = !empty($decision['echeance']) ? $decision['echeance'] : now()->addDays(3)->format('Y-m-d');
                    } else {
                        $actionText = $decision;
                        $responsable = null;
                        $echeance = now()->addDays(3)->format('Y-m-d');
                    }

                    if (trim($actionText) !== '') {
                        \App\Models\Gel\Task::create([
                            'client_id'     => $pv->client_id,
                            'created_by'    => Auth::id(),
                            'titre'         => 'PV – ' . substr($actionText, 0, 60),
                            'description'   => "Décision issue du PV \"{$pv->titre}\" :\n{$actionText}" . ($responsable ? "\n\nResponsable : {$responsable}" : ''),
                            'statut'        => 'a_faire',
                            'priorite'      => 'haute',
                            'date_echeance' => $echeance,
                        ]);
                    }
                }
            }
        }

        $pv->update($validated);

        $taskCount = is_array($pv->fresh()->decisions) ? count(array_filter($pv->fresh()->decisions)) : 0;
        $message = 'Statut du PV mis à jour.';
        if ($validated['statut'] === 'signe_archive') {
            $message .= " {$taskCount} tâche(s) automatique(s) créée(s) depuis les décisions.";
        }

        return redirect()->back()->with('success', $message);
    }

    public function extractPv(Request $request)
    {
        $request->validate([
            'text' => 'required|string|max:10000'
        ]);

        $aiService = new AnthropicService();
        $prompt = "Voici les notes brutes d'une réunion :\n\n\"{$request->text}\"\n\n";
        $prompt .= "Extrais les informations suivantes au format JSON strictement :\n";
        $prompt .= "- titre: le titre de la réunion\n";
        $prompt .= "- date_reunion: la date de la réunion au format YYYY-MM-DD (ou null)\n";
        $prompt .= "- heure_debut: heure de début au format HH:MM (ou null)\n";
        $prompt .= "- heure_fin: heure de fin au format HH:MM (ou null)\n";
        $prompt .= "- lieu: le lieu (ou null)\n";
        $prompt .= "- participants: un tableau de chaînes (noms)\n";
        $prompt .= "- ordre_du_jour: un tableau de chaînes (sujets)\n";
        $prompt .= "- decisions: un tableau d'objets avec { action: '...', responsable: '...', echeance: 'YYYY-MM-DD' }\n";

        $system = "Tu es un assistant IA d'extraction pour le secrétariat. Ne renvoie QUE du JSON valide.";
        
        $result = $aiService->generateJson($prompt, $system);
        
        if (!$result || isset($result['error'])) {
            return response()->json(['error' => 'Extraction échouée'], 500);
        }
        
        AuditLogService::log('IA ACTION', Auth::user(), null, ['action' => 'Extraction PV Saisie Rapide']);
        
        return response()->json($result);
    }
}


