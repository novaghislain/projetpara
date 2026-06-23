<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\BusinessDomain;
use App\Models\Client;
use App\Models\User;
use App\Models\UserClient;
use App\Services\TenantDomainService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CompanyRegistrationController extends Controller
{
    /**
     * Affiche une étape du wizard.
     */
    public function step(int $step)
    {
        if ($step < 1 || $step > 5) {
            return redirect()->route('register.company.step', ['step' => 1]);
        }

        // Données partagées avec la vue
        $props = [
            'step' => $step,
            'data' => session('company_registration', []),
            'domains' => BusinessDomain::active()->get(),
        ];

        $pageMap = [
            1 => 'register-step1',
            2 => 'register-step2',
            3 => 'register-step3',
            4 => 'register-step4',
            5 => 'register-step5',
        ];

        return view('app', [
            'page' => $pageMap[$step],
            'pageProps' => $props,
        ]);
    }

    /**
     * Traite la soumission d'une étape.
     */
    public function process(Request $request, int $step): JsonResponse|RedirectResponse
    {
        $method = 'processStep' . $step;

        if (method_exists($this, $method)) {
            return $this->$method($request);
        }

        return back()->withErrors(['step' => 'Étape invalide.']);
    }

    // ─── Étape 1 : Informations entreprise ─────────────────────────

    protected function processStep1(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'company_name'  => 'required|string|max:255',
            'legal_form'    => 'nullable|string|max:50',
            'rccm'          => 'nullable|string|max:50',
            'ifu'           => 'nullable|string|max:50',
            'address'       => 'nullable|string|max:255',
            'city'          => 'nullable|string|max:100',
            'phone'         => 'nullable|string|max:30',
            'email'         => 'required|email|max:255',
            'website'       => 'nullable|string|max:255',
        ]);

        $data = session('company_registration', []);
        $data['step1'] = $validated;
        session(['company_registration' => $data]);

        return response()->json(['success' => true, 'next_step' => 2]);
    }

    // ─── Étape 2 : Sélection du domaine ───────────────────────────

    protected function processStep2(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'domain_id'   => 'required|exists:business_domains,id',
            'domain_code' => 'required|string|exists:business_domains,code',
        ]);

        $data = session('company_registration', []);
        $data['step2'] = $validated;
        session(['company_registration' => $data]);

        // Charger le domaine pour l'étape suivante
        $domain = BusinessDomain::find($validated['domain_id']);

        return response()->json([
            'success' => true,
            'next_step' => 3,
            'domain' => [
                'label' => $domain->label,
                'modules_count' => count($domain->modules_comptables),
            ],
        ]);
    }

    // ─── Étape 3 : Plan d'abonnement ─────────────────────────────

    protected function processStep3(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'plan'       => 'required|string|in:mensuel,annuel',
            'contract_type' => 'required|string|in:standard,premium',
        ]);

        $data = session('company_registration', []);
        $data['step3'] = $validated;
        session(['company_registration' => $data]);

        return response()->json(['success' => true, 'next_step' => 4]);
    }

    // ─── Étape 4 : Compte administrateur ─────────────────────────

    protected function processStep4(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'admin_name'     => 'required|string|max:255',
            'admin_email'    => 'required|email|max:255|unique:users,email',
            'admin_phone'    => 'nullable|string|max:30',
            'password'       => 'required|string|min:8|confirmed',
        ]);

        $data = session('company_registration', []);
        $data['step4'] = $validated;
        session(['company_registration' => $data]);

        return response()->json(['success' => true, 'next_step' => 5]);
    }

    // ─── Étape 5 : Confirmation et création ──────────────────────

    protected function processStep5(Request $request): JsonResponse|RedirectResponse
    {
        $data = session('company_registration');

        // Vérifier que toutes les étapes sont complètes
        if (!$data || !isset($data['step1'], $data['step2'], $data['step3'], $data['step4'])) {
            return response()->json([
                'success' => false,
                'message' => 'Toutes les étapes doivent être complétées.',
                'reset' => true,
            ], 422);
        }

        try {
            $result = DB::transaction(function () use ($data) {
                // 1. Créer le client
                $client = Client::create([
                    'company_name' => $data['step1']['company_name'],
                    'legal_form'   => $data['step1']['legal_form'] ?? null,
                    'rccm'         => $data['step1']['rccm'] ?? null,
                    'ifu'          => $data['step1']['ifu'] ?? null,
                    'address'      => $data['step1']['address'] ?? null,
                    'city'         => $data['step1']['city'] ?? null,
                    'phone'        => $data['step1']['phone'] ?? null,
                    'email'        => $data['step1']['email'] ?? null,
                    'website'      => $data['step1']['website'] ?? null,
                    'status'       => 'actif',
                    'contract_type' => $data['step3']['contract_type'],
                    'domain_id'    => $data['step2']['domain_id'],
                    'domain_code'  => $data['step2']['domain_code'],
                ]);

                // 2. Créer l'utilisateur admin
                $admin = User::create([
                    'name'            => $data['step4']['admin_name'],
                    'email'           => $data['step4']['admin_email'],
                    'phone'           => $data['step4']['admin_phone'] ?? null,
                    'password'        => Hash::make($data['step4']['password']),
                    'role'            => 'company_admin',
                    'is_company_admin' => true,
                    'client_id'       => $client->id,
                ]);

                // 3. Associer l'admin au client
                UserClient::create([
                    'user_id'   => $admin->id,
                    'client_id' => $client->id,
                    'role'      => 'company_admin',
                    'is_active' => true,
                    'joined_at' => now(),
                ]);

                // 4. Activer les modules comptables du domaine
                try {
                    app(TenantDomainService::class)->activerModulesDomaine($client);
                } catch (\Exception $e) {
                    // Log mais ne pas bloquer la création
                    logger()->warning('Échec activation modules domaine', [
                        'client_id' => $client->id,
                        'error' => $e->getMessage(),
                    ]);
                }

                // 5. Connecter l'utilisateur
                Auth::login($admin);

                return [
                    'client_id' => $client->id,
                    'name' => $client->company_name,
                ];
            });

            // Nettoyer la session
            session()->forget('company_registration');

            return response()->json([
                'success' => true,
                'message' => 'Votre entreprise a été créée avec succès !',
                'redirect' => '/company/dashboard',
                'company' => $result,
            ]);
        } catch (\Exception $e) {
            logger()->error('Erreur création entreprise', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la création : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupère les domaines disponibles (pour l'étape 2 via API).
     */
    public function getDomains()
    {
        return response()->json(
            BusinessDomain::active()->get(['id', 'code', 'label', 'description', 'icon'])
        );
    }
}
