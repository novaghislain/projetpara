<?php

namespace App\Http\Controllers\GelSuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Services\AuditLogService;

class ItTeamController extends Controller
{
    public function index()
    {
        $informaticiens = User::role('informaticien')->with('permissions')->get();
        
        $modules = [
            'it.tickets' => 'Tickets Techniques',
            'it.security' => 'Sécurité et accès',
            'it.health' => 'Santé technique',
            'it.backups' => 'Sauvegardes et maintenance',
            'it.dev_requests' => 'Demandes de développement client',
            'it.missions' => 'Missions Commerciales IT (Secu, Maint, Dev)',
            'it.equipment' => 'Commandes d\'équipements IT',
        ];
        $actions = ['view', 'create', 'update', 'delete', 'export', 'validate'];

        return view('gel-super-admin.it-team.index', compact('informaticiens', 'modules', 'actions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string',
        ]);

        $password = Str::random(12);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($password),
            'account_type' => 'informaticien',
            'is_active' => true,
        ]);

        $user->assignRole('informaticien');

        AuditLogService::log('it_team.created', $user, null, ['email' => $user->email]);

        // Optionnel : Envoyer un email avec le mot de passe ($password)

        return back()->with('success', 'Informaticien créé avec succès. Mot de passe temporaire : ' . $password);
    }

    public function updatePermissions(Request $request, $id)
    {
        $user = User::role('informaticien')->findOrFail($id);
        
        $permissions = $request->input('permissions', []);
        $user->syncPermissions($permissions);

        AuditLogService::log('it_team.permissions_updated', $user, null, ['permissions' => $permissions]);

        return back()->with('success', 'Permissions mises à jour pour ' . $user->name);
    }

    public function destroy($id)
    {
        $user = User::role('informaticien')->findOrFail($id);
        $name = $user->name;
        $user->delete();

        AuditLogService::log('it_team.deleted', null, null, ['name' => $name]);

        return back()->with('success', 'Le compte de ' . $name . ' a été supprimé.');
    }
}
