<?php
namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Client;
use App\Models\ChartAccount;
use App\Models\Journal;
use App\Models\UserClient;
use Database\Seeders\SyscohadaChartSeeder;
use Database\Seeders\DemoJournalSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Contrôleur API d'inscription libre-service.
 *
 * Crée un nouveau locataire (tenant) avec son plan comptable SYSCOHADA,
 * ses journaux par défaut, son administrateur, et génère un token Sanctum.
 */
class RegisterController extends Controller
{
    /**
     * Inscription libre-service d'un nouveau client ComptaSaaS.
     *
     * Cette méthode :
     * 1. Crée le Tenant (entreprise locataire)
     * 2. Crée l'utilisateur administrateur
     * 3. Installe le plan comptable SYSCOHADA (chart_accounts)
     * 4. Crée les journaux par défaut (accounting_journals)
     * 5. Crée un Client pour rétrocompatibilité
     * 6. Génère un token Sanctum (connexion automatique)
     * 7. Envoie un email de bienvenue
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            // Informations entreprise
            'company_name'    => 'required|string|max:255',
            'company_slug'    => 'required|string|max:100|alpha_dash|unique:tenants,slug',
            'company_email'   => 'required|email|max:255|unique:tenants,email',
            'company_phone'   => 'nullable|string|max:50',
            'company_address' => 'nullable|string|max:500',
            'company_country' => 'nullable|string|max:100',
            'company_city'    => 'nullable|string|max:100',

            // Informations utilisateur (gérant)
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',

            // Acceptation conditions
            'accept_terms' => 'required|accepted',
        ]);

        return DB::transaction(function () use ($validated) {
            // 1. Créer le Tenant (entreprise locataire)
            $tenant = Tenant::create([
                'name'              => $validated['company_name'],
                'slug'              => $validated['company_slug'],
                'email'             => $validated['company_email'],
                'phone'             => $validated['company_phone'] ?? null,
                'address'           => $validated['company_address'] ?? null,
                'country'           => $validated['company_country'] ?? 'Bénin',
                'currency'          => 'XOF',
                'default_vat_rate'  => 18.00,
                'fiscal_year_start' => now()->startOfYear(),
                'fiscal_year_end'   => now()->endOfYear(),
                'is_active'         => true,
                'settings'          => [
                    'city'                => $validated['company_city'] ?? null,
                    'domain'              => 'comptasaas',
                    'registration_source' => 'self_registration',
                ],
            ]);

            // 2. Créer le Client (entreprise cliente pour le portail company)
            $client = Client::create([
                'company_name' => $validated['company_name'],
                'email'        => $validated['company_email'],
                'phone'        => $validated['company_phone'] ?? null,
                'address'      => $validated['company_address'] ?? null,
                'city'         => $validated['company_city'] ?? null,
                'country'      => $validated['company_country'] ?? 'Bénin',
                'status'       => 'actif',
            ]);

            // 3. Créer l'utilisateur administrateur
            $user = User::create([
                'tenant_id'          => $tenant->id,
                'client_id'          => $client->id,
                'name'               => $validated['name'],
                'email'              => $validated['email'],
                'password'           => Hash::make($validated['password']),
                'role'               => 'company_admin',
                'is_company_admin'   => true,
                'is_active'          => true,
                'email_verified_at'  => now(),
            ]);

            // 4. Lier l'utilisateur au client (multi-entreprise)
            UserClient::create([
                'user_id'    => $user->id,
                'client_id'  => $client->id,
                'role'       => 'company_admin',
                'is_active'  => true,
                'joined_at'  => now(),
            ]);

            // 5. Installer le plan comptable SYSCOHADA dans chart_accounts
            $this->installChartAccounts($tenant->id);

            // 6. Créer les journaux par défaut
            $journalData = [
                ['code' => 'OD', 'label' => 'Opérations Diverses',        'type' => 'operations_diverses', 'prefix' => 'OD'],
                ['code' => 'VE', 'label' => 'Ventes',                     'type' => 'ventes',              'prefix' => 'VE'],
                ['code' => 'AC', 'label' => 'Achats',                     'type' => 'achats',              'prefix' => 'AC'],
                ['code' => 'BQ', 'label' => 'Banque',                     'type' => 'banque',              'prefix' => 'BQ'],
                ['code' => 'CA', 'label' => 'Caisse',                     'type' => 'caisse',              'prefix' => 'CA'],
                ['code' => 'AN', 'label' => 'À Nouveaux',                 'type' => 'a_nouveaux',          'prefix' => 'AN'],
                ['code' => 'SA', 'label' => 'Salaires',                   'type' => 'salaires',            'prefix' => 'SA'],
            ];

            $createdJournals = [];
            foreach ($journalData as $j) {
                $createdJournals[$j['code']] = Journal::create([
                    'tenant_id'   => $tenant->id,
                    'code'        => $j['code'],
                    'label'       => $j['label'],
                    'type'        => $j['type'],
                    'prefix'      => $j['prefix'],
                    'is_active'   => true,
                    'next_number' => 1,
                ]);
            }

            // 7. Générer le token Sanctum (connexion automatique)
            $token = $user->createToken('inscription-' . $tenant->slug)->plainTextToken;

            // 8. Envoyer email de bienvenue
            try {
                Mail::to($user->email)->send(new \App\Mail\WelcomeMail($user, $tenant));
            } catch (\Exception $e) {
                Log::warning("Email de bienvenue non envoyé à {$user->email} : " . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Compte créé avec succès. Bienvenue sur ComptaSaaS !',
                'data'    => [
                    'tenant' => [
                        'id'   => $tenant->id,
                        'name' => $tenant->name,
                        'slug' => $tenant->slug,
                    ],
                    'user' => [
                        'id'    => $user->id,
                        'name'  => $user->name,
                        'email' => $user->email,
                        'role'  => $user->role,
                    ],
                    'access_token' => $token,
                    'token_type'   => 'Bearer',
                ],
            ], 201);
        });
    }

    /**
     * Installe le plan comptable SYSCOHADA complet pour un tenant.
     * Crée les comptes des classes 1 à 8 (capitaux, immobilisations,
     * stocks, tiers, trésorerie, charges, produits, comptes spéciaux).
     *
     * @param  int  $tenantId  L'ID du tenant (entreprise)
     */
    private function installChartAccounts(int $tenantId): void
    {
        $accounts = [
            // ─── CLASSE 1 : CAPITAUX ──────────────────────────
            ['code' => '101000', 'name' => 'Capital social',                     'type' => 'passive',  'class' => '1'],
            ['code' => '101100', 'name' => 'Capital individuel',                'type' => 'passive',  'class' => '1'],
            ['code' => '101200', 'name' => 'Capital société',                   'type' => 'passive',  'class' => '1'],
            ['code' => '101300', 'name' => 'Apporteurs',                        'type' => 'passive',  'class' => '1'],
            ['code' => '106000', 'name' => 'Réserves légales',                  'type' => 'passive',  'class' => '1'],
            ['code' => '106100', 'name' => 'Réserves statutaires',              'type' => 'passive',  'class' => '1'],
            ['code' => '106200', 'name' => 'Réserves facultatives',             'type' => 'passive',  'class' => '1'],
            ['code' => '106300', 'name' => 'Plus-values de réévaluation',       'type' => 'passive',  'class' => '1'],
            ['code' => '110000', 'name' => 'Report à nouveau (solde créditeur)','type' => 'passive',  'class' => '1'],
            ['code' => '119000', 'name' => 'Report à nouveau (solde débiteur)', 'type' => 'active',   'class' => '1'],
            ['code' => '120000', 'name' => "Résultat de l'exercice (bénéfice)", 'type' => 'passive',  'class' => '1'],
            ['code' => '129000', 'name' => "Résultat de l'exercice (perte)",    'type' => 'active',   'class' => '1'],
            ['code' => '131000', 'name' => "Subventions d'investissement",      'type' => 'passive',  'class' => '1'],
            ['code' => '141000', 'name' => 'Emprunts obligataires',             'type' => 'passive',  'class' => '1'],
            ['code' => '161000', 'name' => 'Emprunts bancaires',                'type' => 'passive',  'class' => '1'],
            ['code' => '164000', 'name' => 'Découverts bancaires',              'type' => 'passive',  'class' => '1'],
            ['code' => '166000', 'name' => 'Crédits de trésorerie',             'type' => 'passive',  'class' => '1'],
            ['code' => '168000', 'name' => 'Autres dettes financières',         'type' => 'passive',  'class' => '1'],

            // ─── CLASSE 2 : IMMOBILISATIONS ───────────────────
            ['code' => '201000', 'name' => "Frais d'établissement",             'type' => 'active',   'class' => '2'],
            ['code' => '202000', 'name' => 'Frais de recherche et développement','type' => 'active',  'class' => '2'],
            ['code' => '203000', 'name' => 'Brevets, licences, marques',        'type' => 'active',   'class' => '2'],
            ['code' => '204000', 'name' => 'Fonds commercial',                  'type' => 'active',   'class' => '2'],
            ['code' => '206000', 'name' => 'Logiciels informatiques',           'type' => 'active',   'class' => '2'],
            ['code' => '211000', 'name' => 'Terrains agricoles',                'type' => 'active',   'class' => '2'],
            ['code' => '212000', 'name' => 'Terrains nus',                      'type' => 'active',   'class' => '2'],
            ['code' => '213000', 'name' => 'Constructions',                     'type' => 'active',   'class' => '2'],
            ['code' => '214000', 'name' => 'Agencements et aménagements',       'type' => 'active',   'class' => '2'],
            ['code' => '215000', 'name' => 'Matériel et outillage industriels', 'type' => 'active',   'class' => '2'],
            ['code' => '218100', 'name' => 'Matériel informatique',             'type' => 'active',   'class' => '2'],
            ['code' => '218200', 'name' => 'Mobilier de bureau',                'type' => 'active',   'class' => '2'],
            ['code' => '218300', 'name' => 'Matériel de transport',             'type' => 'active',   'class' => '2'],
            ['code' => '281000', 'name' => 'Amortissements frais établissement','type' => 'active',   'class' => '2'],
            ['code' => '282000', 'name' => 'Amortissements immobilisations',    'type' => 'active',   'class' => '2'],
            ['code' => '283000', 'name' => 'Amortissements constructions',      'type' => 'active',   'class' => '2'],
            ['code' => '284000', 'name' => 'Amortissements matériel et outillage','type' => 'active', 'class' => '2'],
            ['code' => '285000', 'name' => 'Amortissements matériel transport', 'type' => 'active',   'class' => '2'],
            ['code' => '286000', 'name' => 'Amortissements mobilier bureau',    'type' => 'active',   'class' => '2'],
            ['code' => '291000', 'name' => 'Provisions dépréciation immob.',    'type' => 'active',   'class' => '2'],

            // ─── CLASSE 3 : STOCKS ────────────────────────────
            ['code' => '311000', 'name' => 'Marchandises',                      'type' => 'active',   'class' => '3'],
            ['code' => '312000', 'name' => 'Matières premières',                'type' => 'active',   'class' => '3'],
            ['code' => '315000', 'name' => 'Produits finis',                    'type' => 'active',   'class' => '3'],
            ['code' => '391000', 'name' => 'Provisions dépréciation stocks',    'type' => 'active',   'class' => '3'],

            // ─── CLASSE 4 : TIERS ─────────────────────────────
            ['code' => '401000', 'name' => 'Fournisseurs',                      'type' => 'passive',  'class' => '4'],
            ['code' => '401100', 'name' => 'Fournisseurs locaux',               'type' => 'passive',  'class' => '4'],
            ['code' => '401200', 'name' => 'Fournisseurs étrangers',            'type' => 'passive',  'class' => '4'],
            ['code' => '404000', 'name' => "Fournisseurs d'immobilisations",    'type' => 'passive',  'class' => '4'],
            ['code' => '408000', 'name' => 'Fournisseurs factures non parvenues','type' => 'passive', 'class' => '4'],
            ['code' => '409000', 'name' => 'Avances fournisseurs',              'type' => 'active',   'class' => '4'],
            ['code' => '411000', 'name' => 'Clients',                           'type' => 'active',   'class' => '4'],
            ['code' => '411100', 'name' => 'Clients locaux',                    'type' => 'active',   'class' => '4'],
            ['code' => '411200', 'name' => 'Clients étrangers',                 'type' => 'active',   'class' => '4'],
            ['code' => '416000', 'name' => 'Créances litigieuses',              'type' => 'active',   'class' => '4'],
            ['code' => '418000', 'name' => 'Clients produits non facturés',     'type' => 'active',   'class' => '4'],
            ['code' => '419000', 'name' => 'Avances clients',                   'type' => 'passive',  'class' => '4'],
            ['code' => '421000', 'name' => 'Personnel rémunérations dues',      'type' => 'passive',  'class' => '4'],
            ['code' => '423000', 'name' => 'Organismes sociaux',                'type' => 'passive',  'class' => '4'],
            ['code' => '431000', 'name' => "État impôts et taxes",              'type' => 'passive',  'class' => '4'],
            ['code' => '431100', 'name' => 'État TVA collectée',                'type' => 'passive',  'class' => '4'],
            ['code' => '431200', 'name' => 'État TVA récupérable',              'type' => 'active',   'class' => '4'],
            ['code' => '431300', 'name' => 'État impôt sur bénéfices',          'type' => 'passive',  'class' => '4'],
            ['code' => '432000', 'name' => 'Autres impôts et taxes',            'type' => 'passive',  'class' => '4'],
            ['code' => '441000', 'name' => 'Actionnaires ou associés',          'type' => 'passive',  'class' => '4'],
            ['code' => '444000', 'name' => 'Dettes sur acquisitions',           'type' => 'passive',  'class' => '4'],
            ['code' => '471000', 'name' => "Comptes d'attente",                 'type' => 'active',   'class' => '4'],
            ['code' => '472000', 'name' => "Charges constatées d'avance",       'type' => 'active',   'class' => '4'],
            ['code' => '478000', 'name' => "Produits constatés d'avance",       'type' => 'passive',  'class' => '4'],
            ['code' => '481000', 'name' => "Créances sur l'État",               'type' => 'active',   'class' => '4'],

            // ─── CLASSE 5 : TRÉSORERIE ────────────────────────
            ['code' => '511000', 'name' => 'Banques locales',                   'type' => 'active',   'class' => '5'],
            ['code' => '511100', 'name' => 'Société Générale',                  'type' => 'active',   'class' => '5'],
            ['code' => '511200', 'name' => 'Ecobank',                           'type' => 'active',   'class' => '5'],
            ['code' => '511300', 'name' => 'BOA',                               'type' => 'active',   'class' => '5'],
            ['code' => '512000', 'name' => 'Banques étrangères',                'type' => 'active',   'class' => '5'],
            ['code' => '521000', 'name' => 'Caisse',                            'type' => 'active',   'class' => '5'],
            ['code' => '521100', 'name' => 'Caisse principale',                 'type' => 'active',   'class' => '5'],
            ['code' => '521200', 'name' => 'Caisse auxiliaire',                 'type' => 'active',   'class' => '5'],
            ['code' => '531000', 'name' => 'Règlements par carte',              'type' => 'active',   'class' => '5'],
            ['code' => '541000', 'name' => 'Chèques à encaisser',               'type' => 'active',   'class' => '5'],
            ['code' => '581000', 'name' => 'Virements internes',                'type' => 'active',   'class' => '5'],

            // ─── CLASSE 6 : CHARGES ───────────────────────────
            ['code' => '601000', 'name' => 'Achats de marchandises',            'type' => 'charge',   'class' => '6'],
            ['code' => '602000', 'name' => 'Achats de matières premières',      'type' => 'charge',   'class' => '6'],
            ['code' => '603000', 'name' => 'Achats de fournitures',             'type' => 'charge',   'class' => '6'],
            ['code' => '604000', 'name' => 'Variations des stocks',             'type' => 'charge',   'class' => '6'],
            ['code' => '611000', 'name' => 'Transports',                        'type' => 'charge',   'class' => '6'],
            ['code' => '612000', 'name' => 'Location',                          'type' => 'charge',   'class' => '6'],
            ['code' => '613000', 'name' => 'Entretien et réparations',          'type' => 'charge',   'class' => '6'],
            ['code' => '614000', 'name' => 'Assurances',                        'type' => 'charge',   'class' => '6'],
            ['code' => '615000', 'name' => 'Documentation',                     'type' => 'charge',   'class' => '6'],
            ['code' => '616000', 'name' => 'Personnel extérieur',               'type' => 'charge',   'class' => '6'],
            ['code' => '621000', 'name' => 'Redevances crédit-bail',            'type' => 'charge',   'class' => '6'],
            ['code' => '631000', 'name' => 'Frais bancaires',                   'type' => 'charge',   'class' => '6'],
            ['code' => '632000', 'name' => 'Commissions bancaires',             'type' => 'charge',   'class' => '6'],
            ['code' => '641000', 'name' => 'Salaires appointements',            'type' => 'charge',   'class' => '6'],
            ['code' => '642000', 'name' => 'Charges sociales',                  'type' => 'charge',   'class' => '6'],
            ['code' => '643000', 'name' => 'Indemnités',                        'type' => 'charge',   'class' => '6'],
            ['code' => '651000', 'name' => 'Impôts et taxes',                   'type' => 'charge',   'class' => '6'],
            ['code' => '652000', 'name' => 'Taxe sur valeur ajoutée',           'type' => 'charge',   'class' => '6'],
            ['code' => '661000', 'name' => 'Intérêts des emprunts',             'type' => 'charge',   'class' => '6'],
            ['code' => '671000', 'name' => 'Charges exceptionnelles',           'type' => 'charge',   'class' => '6'],
            ['code' => '681000', 'name' => 'Dotations aux amortissements',      'type' => 'charge',   'class' => '6'],
            ['code' => '691000', 'name' => 'Dotations aux provisions',          'type' => 'charge',   'class' => '6'],

            // ─── CLASSE 7 : PRODUITS ──────────────────────────
            ['code' => '701000', 'name' => 'Ventes de marchandises',            'type' => 'produit',  'class' => '7'],
            ['code' => '702000', 'name' => 'Ventes de produits finis',          'type' => 'produit',  'class' => '7'],
            ['code' => '711000', 'name' => 'Prestations de services',           'type' => 'produit',  'class' => '7'],
            ['code' => '712000', 'name' => 'Commissions',                       'type' => 'produit',  'class' => '7'],
            ['code' => '721000', 'name' => 'Produits accessoires',              'type' => 'produit',  'class' => '7'],
            ['code' => '741000', 'name' => "Subventions d'exploitation",        'type' => 'produit',  'class' => '7'],
            ['code' => '751000', 'name' => 'Produits financiers',               'type' => 'produit',  'class' => '7'],
            ['code' => '752000', 'name' => 'Intérêts',                          'type' => 'produit',  'class' => '7'],
            ['code' => '761000', 'name' => 'Produits exceptionnels',            'type' => 'produit',  'class' => '7'],
            ['code' => '771000', 'name' => 'Reprises sur amortissements',       'type' => 'produit',  'class' => '7'],
            ['code' => '781000', 'name' => 'Reprises sur provisions',           'type' => 'produit',  'class' => '7'],

            // ─── CLASSE 8 : COMPTES SPÉCIAUX ──────────────────
            ['code' => '801000', 'name' => 'Engagements donnés',                'type' => 'passive',  'class' => '8'],
            ['code' => '802000', 'name' => 'Engagements reçus',                 'type' => 'passive',  'class' => '8'],
            ['code' => '890000', 'name' => "Bilan d'ouverture",                 'type' => 'passive',  'class' => '8'],
            ['code' => '891000', 'name' => "Bilan de clôture",                  'type' => 'passive',  'class' => '8'],
        ];

        DB::transaction(function () use ($tenantId, $accounts) {
            foreach ($accounts as $acc) {
                ChartAccount::create([
                    'tenant_id'    => $tenantId,
                    'code'         => $acc['code'],
                    'name'         => $acc['name'],
                    'type'         => $acc['type'],
                    'class'        => $acc['class'],
                    'is_active'    => true,
                    'is_syscohada' => true,
                ]);
            }
        });
    }
}
