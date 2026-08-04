<?php

namespace App\Http\Controllers\GelSuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AuditTrail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SecurityController extends Controller
{
    public function index()
    {
        $superAdmins = User::where('role', 'super_admin')->get();
        
        $failedLogins = AuditTrail::where('event', 'LOGIN_FAILED')
                                  ->orderBy('created_at', 'desc')
                                  ->take(10)
                                  ->get();
                                  
        $criticalActions = AuditTrail::whereIn('event', ['SUSPEND_TENANT', 'DELETE_TENANT', 'IMPERSONATE_START', 'DELETE_PLAN'])
                                     ->orderBy('created_at', 'desc')
                                     ->take(10)
                                     ->get();

        return view('gel-super-admin.security.index', compact('superAdmins', 'failedLogins', 'criticalActions'));
    }

    public function storeAdmin(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'super_admin',
            'account_type' => 'super_admin',
            'is_admin' => true,
        ]);

        AuditTrail::create([
            'user_id' => auth()->id(),
            'event' => 'CREATE_SUPER_ADMIN',
            'description' => "Création du super administrateur {$user->email}.",
            'ip_address' => request()->ip(),
            'auditable_type' => User::class,
            'auditable_id' => $user->id
        ]);

        return back()->with('success', 'Le super administrateur a été créé.');
    }

    public function revokeAdmin($id)
    {
        $user = User::findOrFail($id);
        
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas révoquer votre propre compte.');
        }

        $user->is_suspended = true;
        $user->save();

        AuditTrail::create([
            'user_id' => auth()->id(),
            'event' => 'REVOKE_SUPER_ADMIN',
            'description' => "Révocation du super administrateur {$user->email}.",
            'ip_address' => request()->ip(),
            'auditable_type' => User::class,
            'auditable_id' => $user->id
        ]);

        return back()->with('success', 'L\'accès du super administrateur a été révoqué.');
    }
}
