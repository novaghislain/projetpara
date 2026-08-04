<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\PortalContact;
use App\Models\Client;

class AuthController extends Controller
{
    /**
     * Helper to get client by slug
     */
    private function getClientBySlug($slug)
    {
        $client = Client::where('portal_slug', $slug)->firstOrFail();
        if (!$client->portal_active) {
            abort(403, 'Le portail de cette entreprise est actuellement désactivé.');
        }
        return $client;
    }

    /**
     * Afficher le formulaire de connexion
     */
    public function showLoginForm($slug)
    {
        $client = $this->getClientBySlug($slug);
        return view('portal.auth.login', compact('client', 'slug'));
    }

    /**
     * Traiter la connexion
     */
    public function login(Request $request, $slug)
    {
        $client = $this->getClientBySlug($slug);

        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::guard('portal')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            // Si le contact se connecte, on vérifie s'il est bien attaché à ce client
            $user = Auth::guard('portal')->user();
            if (!$user->clients()->where('client_id', $client->id)->exists()) {
                // Si non, c'est étrange (peut-être qu'il existe mais pour un autre cabinet). 
                // Pour simplifier l'auto-souveraineté, on l'attache automatiquement à ce client.
                $user->clients()->attach($client->id, ['is_active' => true]);
            }

            return redirect()->intended(route('portal.dashboard', ['slug' => $slug]));
        }

        return back()->withErrors([
            'email' => 'Les identifiants fournis ne correspondent pas à nos enregistrements.',
        ])->onlyInput('email');
    }

    /**
     * Afficher le formulaire d'inscription
     */
    public function showRegisterForm($slug)
    {
        $client = $this->getClientBySlug($slug);
        return view('portal.auth.register', compact('client', 'slug'));
    }

    /**
     * Traiter l'inscription
     */
    public function register(Request $request, $slug)
    {
        $client = $this->getClientBySlug($slug);

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Vérifier si le contact existe déjà dans TOUT le portail (multi-tenant par e-mail)
        $contact = PortalContact::where('email', $request->email)->first();

        if (!$contact) {
            $contact = PortalContact::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
        } else {
            // Optionnel: Vérifier que le mot de passe est bon si le compte existe déjà
            // Mais pour une simple inscription on risque de réécraser ?
            // L'idéal : dire "Ce compte existe déjà, veuillez vous connecter".
            if (!Auth::guard('portal')->attempt(['email' => $request->email, 'password' => $request->password])) {
                return back()->withErrors([
                    'email' => 'Un compte existe déjà avec cet email, mais le mot de passe est incorrect. Veuillez vous connecter.'
                ])->onlyInput('email');
            }
        }

        // Attacher le contact à l'entreprise courante s'il n'y est pas
        if (!$contact->clients()->where('client_id', $client->id)->exists()) {
            $contact->clients()->attach($client->id, ['is_active' => true]);
        }

        Auth::guard('portal')->login($contact);
        $request->session()->regenerate();

        return redirect()->route('portal.dashboard', ['slug' => $slug]);
    }

    /**
     * Déconnexion
     */
    public function logout(Request $request, $slug)
    {
        Auth::guard('portal')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('portal.login', ['slug' => $slug]);
    }
}
