<?php

namespace App\Http\Controllers\GelSecretary\Documents;

use App\Http\Controllers\Controller;
use App\Models\Gel\Client;
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

        $tab = request('tab', 'arrivee');
        if ($tab === 'arrivee') {
            $query->where('type', 'entrant');
        } elseif ($tab === 'depart') {
            $query->where('type', 'sortant');
        } elseif ($tab === 'interne') {
            $query->where('type', 'interne');
        }

        $courriers = $query->orderBy('created_at', 'desc')->paginate(20);
        $clients = Client::orderBy('nom_entreprise')->get();
        $activeClient = $activeClientId ? Client::find($activeClientId) : $clients->first();
        $stats = [
            'entrants_non_traites' => DaeCourrier::where('type', 'entrant')->where('statut', 'recu')->count(),
            'sortants_brouillons' => DaeCourrier::where('type', 'sortant')->where('statut', 'brouillon')->count(),
        ];

        return view('gel-secretary.courriers.index', compact('courriers', 'clients', 'activeClient', 'tab', 'stats'));
    }

    public function exportPdf(Request $request)
    {
        $user = Auth::user();
        
        $query = DaeCourrier::with(['client']);
            
        $activeClientId = session('active_client_id') ?? $user->active_client_id ?? $user->client_id;
        if ($activeClientId) {
            $query->where('client_id', $activeClientId);
        }

        $tab = request('tab', 'arrivee');
        if ($tab === 'arrivee') {
            $query->where('type', 'entrant');
            $title = "REGISTRE ARRIVÉE";
        } elseif ($tab === 'depart') {
            $query->where('type', 'sortant');
            $title = "REGISTRE DÉPART";
        } elseif ($tab === 'interne') {
            $query->where('type', 'interne');
            $title = "REGISTRE GÉNÉRAL / DÉCHARGE";
        }

        $courriers = $query->orderBy('created_at', 'desc')->get();
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('gel-secretary.courriers.pdf', compact('courriers', 'tab', 'title'))
                ->setPaper('a4', 'landscape');
                
        return $pdf->download('registre_' . $tab . '_' . date('Y-m-d') . '.pdf');
    }

    protected function getClientId(Request $request)
    {
        $user = Auth::user();
        return $request->query('client_id') ?? $request->input('client_id') ?? session('active_client_id') ?? $user->active_client_id ?? $user->client_id;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id'     => 'required|exists:clients,id',
            'type'          => 'required|in:entrant,sortant,interne',
            'objet'         => 'required|string|max:255',
            'expediteur'    => 'nullable|string|max:255',
            'destinataire'  => 'nullable|string|max:255',
            'date_courrier' => 'nullable|date',
            'urgence'       => 'nullable|string',
            'contenu'       => 'nullable|string',
            
            // Nouveaux champs registres
            'numero_ordre'  => 'nullable|string|max:255',
            'nombre_pieces' => 'nullable|integer|min:0',
            'date_reception'=> 'nullable|date',
            'date_envoi'    => 'nullable|date',
            'date_reponse'  => 'nullable|date',
            'numero_reponse'=> 'nullable|string|max:255',
            'numero_archives'=> 'nullable|string|max:255',
            'observations'  => 'nullable|string',
            'signature_destinataire' => 'nullable|string|max:255',
            'noms_adresses' => 'nullable|string|max:255',
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

    public function create(Request $request)
    {
        return view('gel-secretary.courriers.create');
    }

    public function show($id)
    {
        $courrier = DaeCourrier::with('client')->findOrFail($id);
        return view('gel-secretary.courriers.show', compact('courrier'));
    }

    public function updateStatut(Request $request, $id)
    {
        $courrier = DaeCourrier::findOrFail($id);
        
        $request->validate([
            'statut' => 'required|in:traite,brouillon,en_cours,archive',
        ]);

        $courrier->statut = $request->statut;
        
        if ($request->statut === 'traite') {
            if ($courrier->type === 'sortant') {
                $courrier->envoye_at = now();
            } else {
                $courrier->date_traitement = now();
            }
        }
        
        $courrier->save();

        return redirect()->back()->with('success', 'Statut mis à jour avec succès.');
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

    public function generateDraft(Request $request, AnthropicService $anthropic)
    {
        $request->validate([
            'objet' => 'required|string',
            'contexte' => 'nullable|string',
        ]);

        $prompt = "Tu es l'assistant de direction (Secrétariat virtuel GEL). Rédige une réponse professionnelle (brouillon) pour le courrier suivant.
Objet original : {$request->objet}
Contexte : {$request->contexte}

Instructions spécifiques : Rédige un brouillon clair et professionnel.";

        $draft = $anthropic->generate($prompt);
        AuditLogService::log('IA ACTION', Auth::user(), null, ['action' => 'Génération Brouillon Courrier', 'objet' => $request->objet]);

        return response()->json(['draft' => $draft]);
    }

    public function analyzeIa(Request $request, AnthropicService $anthropic)
    {
        $request->validate([
            'text_content' => 'nullable|string',
            'image_path' => 'nullable|string',
        ]);

        $prompt = "Voici le texte extrait (ou l'image) d'un courrier physique ou d'un email. 
Extrais les informations suivantes au format JSON strictement :
- type: 'entrant' ou 'sortant'
- expediteur: nom de la personne ou organisation qui envoie
- destinataire: nom de la personne ou organisation qui reçoit
- objet: un objet concis du courrier (max 50 chars)
- urgence: 'normal', 'urgent' ou 'tres_urgent'";

        if ($request->filled('image_path')) {
            // Analyse via vision (image scannée)
            $absolutePath = storage_path('app/public/' . ltrim($request->image_path, '/'));
            $json = $anthropic->generateVisionJson($prompt, $absolutePath, 'image/jpeg');
        } else if ($request->filled('text_content')) {
            // Analyse via texte (copié/collé)
            $prompt .= "\n\nTexte :\n" . $request->text_content;
            $json = $anthropic->generateJson($prompt);
        } else {
            return response()->json(['error' => 'Aucun contenu fourni.'], 400);
        }

        AuditLogService::log('IA ACTION', Auth::user(), null, ['action' => 'OCR Courrier']);

        return response()->json($json ?? [
            'type' => 'entrant',
            'expediteur' => '',
            'destinataire' => '',
            'objet' => '',
            'urgence' => 'normal'
        ]);
    }
}
