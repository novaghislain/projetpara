<?php

namespace App\Http\Controllers\GelSecretary\Documents;

use App\Http\Controllers\Controller;
use App\Models\Gel\Client;
use App\Models\Dae\DaeModeleCourrier;
use App\Models\Dae\DaeCourrier;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * S13 — Centre de modèles.
 *
 * Bibliothèque de modèles (courrier, PV, compte rendu, facture, devis,
 * contrat, rapport, attestation, bon de commande/livraison) avec
 * pré-remplissage automatique des informations entreprise et substitution
 * de variables.
 *
 * Principe : réutilise `dae_modeles_courriers` (existant) ; à la génération
 * on exporte la lettre vers un courrier réel via le module Courriers
 * existant (pas de duplication).
 */
class ModelesController extends Controller
{
    /** Types de modèles disponibles (bibliothèque S13). */
    public const TYPES = [
        'lettre'         => ['label' => 'Lettre',         'icon' => 'fa-envelope',            'color' => '#2563EB'],
        'pv'             => ['label' => 'Procès-verbal',  'icon' => 'fa-file-signature',      'color' => '#6366F1'],
        'compte_rendu'   => ['label' => 'Compte rendu',   'icon' => 'fa-file-lines',          'color' => '#0D9488'],
        'facture'        => ['label' => 'Facture',        'icon' => 'fa-file-invoice-dollar', 'color' => '#059669'],
        'devis'          => ['label' => 'Devis',          'icon' => 'fa-file-invoice',        'color' => '#D97706'],
        'contrat'        => ['label' => 'Contrat',        'icon' => 'fa-file-contract',       'color' => '#7C3AED'],
        'rapport'        => ['label' => 'Rapport',        'icon' => 'fa-chart-line',          'color' => '#EA580C'],
        'attestation'    => ['label' => 'Attestation',    'icon' => 'fa-id-card',             'color' => '#DC2626'],
        'bon_commande'   => ['label' => 'Bon de commande','icon' => 'fa-cart-plus',           'color' => '#0891B2'],
        'bon_livraison'  => ['label' => 'Bon de livraison','icon' => 'fa-truck',              'color' => '#4D7C0F'],
    ];

    /** Variables disponibles, pré-remplies depuis gel_clients. */
    public const VARIABLES = [
        'entreprise'    => 'Raison sociale',
        'forme_juridique' => 'Forme juridique',
        'adresse'       => 'Adresse',
        'telephone'     => 'Téléphone',
        'email'         => 'E-mail',
        'ifu'           => 'IFU',
        'rccm'          => 'RCCM',
        'registre_cc'   => 'Registre du commerce',
        'date_creation' => 'Date de création',
    ];

    public function index(Request $request)
    {
        $user = Auth::user();

        $activeClientId = session('active_client_id') ?? $user->active_client_id ?? $user->client_id;
        $activeClient = $activeClientId ? Client::find($activeClientId) : null;

        // Bibliothèque de modèles officiels pré-créés (dictionnaire, sans données fictives)
        $bibliotheque = [
            'lettre'         => ['sujet' => 'Objet de la lettre', 'template' => "…(sujet)\n\nMadame, Monsieur,\n\n..."],
            'pv'             => ['sujet' => 'Procès-verbal de réunion du {{date}}', 'template' => "Procès-verbal de la réunion du {{date}} à {{lieu}}.\n\nPrésents : {{participants}}\n\nOrdre du jour :\n\nDécisions :\n\nProchaine réunion :"],
            'compte_rendu'   => ['sujet' => 'Compte rendu de la semaine', 'template' => "Compte rendu d'activité.\n\n1. Activités réalisées :\n2. Difficultés :\n3. Prochaines actions :"],
            'facture'        => ['sujet' => 'Facture n°…', 'template' => "FACTURE n°…\n\nClient : {{entreprise}} — IFU : {{ifu}}\n\nDésignation | Quantité | PU | Total"],
            'devis'          => ['sujet' => 'Devis', 'template' => "DEVIS\n\nClient : {{entreprise}}\n\nDésignation | Qté | PU | Total\n\nValidité :"],
            'contrat'        => ['sujet' => 'Contrat', 'template' => "CONTRAT\n\nEntre les soussignés :\n{{entreprise}}, IFU {{ifu}}, RCCM {{rccm}}, sis à {{adresse}},\n\nIl a été convenu ce qui suit :\n\nArticle 1 — Objet :\n…"],
            'rapport'        => ['sujet' => 'Rapport', 'template' => "RAPPORT\n\nPériode :\n\n1. Contexte\n2. Réalisations\n3. Difficultés\n4. Recommandations"],
            'bon_commande'   => ['sujet' => 'Bon de commande', 'template' => "BON DE COMMANDE n°…\n\nFournisseur :\n\nRéf | Désignation | Qté | Prix"],
            'bon_livraison'  => ['sujet' => 'Bon de livraison', 'template' => "BON DE LIVRAISON n°…\n\nClient : {{entreprise}}\n\nDésignation | Qté | Observation"],
        ];

        // Modèles déjà enregistrés
        $modeleQuery = DaeModeleCourrier::query()->orderBy('nom');
        if ($activeClient) $modeleQuery->where('client_id', $activeClient->id);
        $modelesEnregistres = $modeleQuery->get();

        $clients = Client::orderBy('nom_entreprise')->get();

        return view('gel-secretary.modeles.index', compact(
            'activeClient', 'bibliotheque', 'modelesEnregistres', 'clients'
        ));
    }

    /**
     * Génère le contenu d'un modèle avec substitution des variables,
     * pré-remplies avec les infos de l'entreprise cliente.
     */
    public function generer(Request $request, $type)
    {
        $clientId = $request->input('client_id');
        $client = $clientId ? Client::find($clientId) : null;

        $values = [
            'nom'             => $client?->nom_entreprise ?? '',
            'entreprise'      => $client?->nom_entreprise ?? '',
            'forme_juridique' => $client?->forme_juridique ?? '',
            'adresse'         => $client?->adresse ?? '',
            'telephone'       => $client?->telephone ?? '',
            'email'           => $client?->email ?? '',
            'ifu'             => $client?->ifu ?? '',
            'rccm'            => $client?->rccm ?? '',
            'registre_cc'     => $client?->registre_cc ?? '',
            'date_creation'   => $client?->created_at?->format('d/m/Y') ?? '',
            'date'            => now()->format('d/m/Y'),
        ];

        // Compléter avec les valeurs saisies par l'utilisateur (si présentes)
        foreach (self::VARIABLES as $key => $label) {
            if ($request->filled('var_' . $key)) {
                $values[$key] = $request->input('var_' . $key);
            }
        }

        $modele = $this->buildTemplate($request->input('sujet'), $request->input('corps'));
        $corps = $modele['corps'];
        $objet = $modele['objet'];
        foreach ($values as $k => $v) {
            $corps = str_replace('{{' . $k . '}}', (string) $v, $corps);
            $objet = str_replace('{{' . $k . '}}', (string) $v, $objet);
        }

        return response()->json([
            'objet' => $objet,
            'corps' => $corps,
            'type'  => $type,
        ]);
    }

    /**
     * Enregistre un modèle personnalisé en bibliothèque (dae_modeles_courriers).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id'    => 'nullable|exists:clients,id',
            'nom'          => 'required|string|max:255',
            'type'         => 'required|string|max:200',
            'objet_defaut' => 'nullable|string|max:500',
            'corps'        => 'nullable|string',
            'variables'    => 'nullable|json',
            'categorie'    => 'nullable|string|max:200',
        ]);

        $modele = DaeModeleCourrier::create($validated);

        AuditLogService::log('modele.create', $modele, null, [
            'nom' => $modele->nom,
            'type' => $modele->type,
            'client_id' => $modele->client_id,
        ]);

        return redirect()->route('gel-secretary.modeles.index')
            ->with('success', 'Modèle enregistré dans la bibliothèque.');
    }

    /**
     * Génère un courrier réel depuis un modèle (→ module Courants existant).
     */
    public function exporter(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'objet'     => 'required|string|max:500',
            'corps'     => 'required|string',
            'type'      => 'nullable|string',
        ]);

        $courrier = DaeCourrier::create([
            'client_id'       => $validated['client_id'] ?? null,
            'type'            => 'sortant',
            'mode'            => 'email',
            'objet'           => $validated['objet'],
            'contenu'         => $validated['corps'],
            'date_courrier'   => now(),
            'statut'          => 'brouillon',
            'created_by'      => Auth::id(),
        ]);

        AuditLogService::log('modele.export', $courrier, null, [
            'objet' => $courrier->objet,
            'type'  => $validated['type'] ?? null,
        ]);

        return redirect()->route('gel-secretary.courriers.index')
            ->with('success', 'Courrier généré depuis le modèle — retrouvez-le dans Courriers.');
    }

    /** Construit une paire objet/corps depuis les champs du formulaire. */
    protected function buildTemplate($sujet, $corps): array
    {
        return ['objet' => (string) $sujet, 'corps' => (string) $corps];
    }
}


