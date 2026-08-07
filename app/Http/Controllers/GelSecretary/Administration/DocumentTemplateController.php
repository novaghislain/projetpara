<?php

namespace App\Http\Controllers\GelSecretary\Administration;

use App\Http\Controllers\Controller;
use App\Models\DocumentTemplate;
use App\Models\Gel\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentTemplateController extends Controller
{
    // S13 — Bibliothèque de 10 modèles intégrés
    private const DEFAULT_TEMPLATES = [
        ['name' => 'Lettre professionnelle',       'category' => 'Communication',  'description' => 'Modèle de lettre formelle pour toute correspondance externe.'],
        ['name' => 'Procès-Verbal de réunion',     'category' => 'Réunions',       'description' => 'PV type avec ordre du jour, participants, décisions et actions.'],
        ['name' => 'Compte rendu d\'activité',     'category' => 'Rapports',       'description' => 'Compte rendu hebdomadaire ou mensuel des activités réalisées.'],
        ['name' => 'Facture client',               'category' => 'Commercial',     'description' => 'Modèle de facture avec lignes de services, TVA et totaux.'],
        ['name' => 'Devis commercial',             'category' => 'Commercial',     'description' => 'Devis pré-rempli avec coordonnées entreprise et grille de tarification.'],
        ['name' => 'Contrat de prestation',        'category' => 'Juridique',      'description' => 'Contrat type pour des prestations de service entre professionnels.'],
        ['name' => 'Rapport d\'activité',          'category' => 'Rapports',       'description' => 'Rapport complet trimestriel ou annuel pour la direction.'],
        ['name' => 'Attestation de travail',       'category' => 'RH',             'description' => 'Attestation employeur certifiant l\'emploi et la rémunération.'],
        ['name' => 'Bon de commande',              'category' => 'Achats',         'description' => 'Bon de commande fournisseur avec références et conditions.'],
        ['name' => 'Bon de livraison',             'category' => 'Logistique',     'description' => 'Bon de livraison avec liste des articles et signature de réception.'],
    ];

    public function index()
    {
        $user = Auth::user();

        // S13: Initialiser les 10 modèles par défaut s'ils n'existent pas encore
        foreach (self::DEFAULT_TEMPLATES as $tpl) {
            DocumentTemplate::firstOrCreate(
                ['name' => $tpl['name']],
                array_merge($tpl, ['file_path' => null, 'is_active' => true])
            );
        }

        $templates = DocumentTemplate::where('is_active', true)->orderBy('category')->orderBy('name')->get();

        // Clients du cabinet ou clients liés à la secrétaire autonome
        $clients = collect();
        $cabinetId = $user->cabinet_id ?? null;
        if ($cabinetId) {
            $clients = Client::where('cabinet_id', $cabinetId)->orderBy('nom_entreprise')->get();
        } elseif ($user->isAutonomousSecretary()) {
            $linkedIds = $user->userClients()->pluck('client_id');
            $clients   = Client::whereIn('id', $linkedIds)->orderBy('nom_entreprise')->get();
        }

        // Entreprise active (context sidebar)
        $activeClientId = session('active_client_id') ?? $user->active_client_id ?? null;
        $activeClient   = $activeClientId ? Client::find($activeClientId) : null;

        return view('gel-secretary.administration.templates.index', compact('templates', 'clients', 'activeClient'));
    }

    public function generate(Request $request, $id)
    {
        $template = DocumentTemplate::findOrFail($id);
        $user     = Auth::user();
        $client   = null;

        if ($request->filled('client_id')) {
            $client = Client::findOrFail($request->client_id);
        }

        \App\Services\AuditLogService::log('template.generate', $client, null, [
            'template' => $template->name,
            'category' => $template->category,
        ]);

        $clientLabel = $client ? ' pour ' . ($client->nom_entreprise ?? $client->nom_entreprise) : '';
        return back()->with('success', 'Document "' . $template->name . '" pré-rempli généré' . $clientLabel . '. (PDF en préparation)');
    }
}
