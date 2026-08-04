<?php

namespace App\Http\Controllers\GelSecretary\Documents;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Dae\DaeCourrier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\AuditLogService;
use App\Services\AnthropicService;

class CourrierController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $query = DaeCourrier::with(['client']);
            
        $activeClientId = session('active_client_id') ?? $user->active_client_id ?? $user->client_id;
        if ($activeClientId) {
            $query->where('client_id', $activeClientId);
        }

        $tab = request('tab', 'en_cours');
        if ($tab === 'archives') {
            $query->where('workflow_step', 'archive');
        } else {
            $query->where('workflow_step', '!=', 'archive');
        }

        $courriers = $query->orderBy('created_at', 'desc')->get();
        $clients = Client::orderBy('company_name')->get();
        $activeClient = $activeClientId ? Client::find($activeClientId) : $clients->first();

        return view('gel-secretary.courriers.index', compact('courriers', 'clients', 'activeClient', 'tab'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id'     => 'required|exists:clients,id',
            'type'          => 'required|in:entrant,sortant',
            'objet'         => 'required|string|max:255',
            'expediteur'    => 'required|string|max:255',
            'destinataire'  => 'required|string|max:255',
            'date_courrier' => 'nullable|date',
            'urgence'       => 'nullable|string',
            'contenu'       => 'nullable|string',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['statut']       = 'non_traite';
        $validated['workflow_step'] = 'creation';
        
        // Numéro auto-généré : COUR-YYYY-XXXXX
        $year  = date('Y');
        $count = DaeCourrier::whereYear('created_at', $year)->count() + 1;
        $validated['reference'] = 'COUR-' . $year . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);

        DaeCourrier::create($validated);

        return redirect()->back()->with('success', 'Courrier enregistré avec succès.');
    }

    public function show($id)
    {
        $courrier = DaeCourrier::with('client')->findOrFail($id);
        return response()->json($courrier);
    }

    /**
     * Avancement du workflow : chaque étape est tracée avec timestamp + user.
     * Étapes : creation → visa → validation → signature → envoi → archive
     */
    public function update(Request $request, $id)
    {
        $courrier = DaeCourrier::findOrFail($id);
        
        $request->validate([
            'workflow_step'  => 'required|in:creation,visa,validation,signature,envoi,archive',
            'workflow_notes' => 'nullable|string|max:1000',
        ]);

        $newStep = $request->workflow_step;
        $updates = [
            'workflow_step'  => $newStep,
            'workflow_notes' => $request->workflow_notes,
        ];

        // Horodatage de chaque étape
        switch ($newStep) {
            case 'visa':
                $updates['visa_par'] = Auth::id();
                $updates['visa_at']  = now();
                $updates['statut']   = 'en_cours';
                break;
            case 'validation':
                $updates['statut'] = 'en_cours';
                break;
            case 'signature':
                $updates['signe_par'] = Auth::id();
                $updates['signe_at']  = now();
                $updates['statut']    = 'en_cours';
                break;
            case 'envoi':
                $updates['envoye_at']  = now();
                $updates['date_envoi'] = now();
                $updates['statut']     = 'traite';
                $updates['date_traitement'] = now();
                $updates['traite_par'] = Auth::id();
                break;
            case 'archive':
                $updates['archive_at'] = now();
                $updates['statut']     = 'archive';
                break;
        }

        $courrier->update($updates);

        $steps = array_keys(\App\Models\Dae\DaeCourrier::WORKFLOW_STEPS);
        $label = \App\Models\Dae\DaeCourrier::WORKFLOW_STEPS[$newStep]['label'];

        return redirect()->back()->with('success', "Courrier passé à l'étape : {$label}.");
    }

    public function generateDraft(Request $request)
    {
        $request->validate([
            'objet' => 'required|string',
            'contexte' => 'nullable|string'
        ]);

        $aiService = new AnthropicService();
        $prompt = "Tu es un(e) assistant(e) de secrétariat professionnel(le). Rédige le corps d'un courrier formel.\n";
        $prompt .= "Objet : {$request->objet}\n";
        if ($request->filled('contexte')) {
            $prompt .= "Contexte supplémentaire : {$request->contexte}\n";
        }
        $prompt .= "\nNe renvoie QUE le texte du courrier, sans formules d'introduction du type 'Voici le texte :'. Rédige avec un ton formel et professionnel, prêt à être envoyé.";

        $system = "Tu es expert en rédaction administrative et formelle.";
        
        $response = $aiService->generate($prompt, $system, ['max_tokens' => 800]);

        AuditLogService::log('IA ACTION', Auth::user(), null, ['action' => 'Génération Brouillon Courrier', 'objet' => $request->objet]);

        return response()->json(['draft' => $response]);
    }
}
