<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserClient;
use App\Services\CompanyCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;

/**
 * Contrôleur d'inscription des clients finaux via code entreprise.
 *
 * Permet à un utilisateur de créer un compte 'client' lié à une entreprise
 * existante via un code d'accès fourni par celle-ci.
 */
class ClientRegistrationController extends Controller
{
    /**
     * Affiche la page d'inscription avec code entreprise.
     */
    public function create()
    {
        return view('app', [
            'page' => 'client-register',
        ]);
    }

    /**
     * Inscrit un client final avec un code entreprise valide.
     */
    public function store(Request $request): JsonResponse
    {
        // Rate limiting : 5 tentatives par minute par IP + email
        $rateKey = 'client-register:' . $request->ip() . '|' . $request->email;
        if (RateLimiter::tooManyAttempts($rateKey, 5)) {
            return response()->json([
                'success' => false,
                'message' => 'Trop de tentatives. Réessayez dans une minute.',
            ], 429);
        }

        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|max:255|unique:users,email',
            'password'    => 'required|string|min:8|confirmed',
            'phone'       => 'nullable|string|max:30',
            'client_code' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Résoudre le code entreprise
        $codeService = app(CompanyCodeService::class);
        $client = $codeService->resolve($request->client_code);

        if (!$client) {
            RateLimiter::hit($rateKey, 60);
            return response()->json([
                'success' => false,
                'errors' => ['client_code' => ['Code entreprise invalide. Vérifiez auprès de votre entreprise.']],
            ], 422);
        }

        try {
            $result = DB::transaction(function () use ($request, $client, $codeService) {
                // Vérifier que l'utilisateur n'est pas déjà lié
                // (cas rare : même email déjà lié — protection supplémentaire)
                $existingUser = User::where('email', $request->email)->first();

                if ($existingUser) {
                    if (!$codeService->canAttachUser($existingUser->id, $client->id)) {
                        throw new \RuntimeException('Vous êtes déjà rattaché à cette entreprise.');
                    }
                    // L'utilisateur existe déjà (autre entreprise) → on l'attache seulement
                    $user = $existingUser;
                } else {
                    // Créer le nouvel utilisateur
                    $user = User::create([
                        'name'     => $request->name,
                        'email'    => $request->email,
                        'phone'    => $request->phone,
                        'password' => Hash::make($request->password),
                        'role'     => 'client',
                    ]);
                }

                // Attacher au client
                UserClient::create([
                    'user_id'   => $user->id,
                    'client_id' => $client->id,
                    'role'      => 'client',
                    'is_active' => true,
                    'joined_at' => now(),
                ]);

                return $user;
            });

            // Connecter l'utilisateur
            Auth::login($result);

            RateLimiter::clear($rateKey);

            return response()->json([
                'success'  => true,
                'message'  => 'Compte créé avec succès ! Vous êtes rattaché à ' . $client->company_name . '.',
                'redirect' => $result->isComptable() ? '/cpa/dashboard' : '/dashboard',
            ]);

        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'errors'  => ['client_code' => [$e->getMessage()]],
            ], 422);
        } catch (\Exception $e) {
            logger()->error('Erreur inscription client avec code', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'inscription.',
            ], 500);
        }
    }
}
