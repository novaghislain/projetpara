<?php

namespace App\Http\Controllers\GelAdmin\Team;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    use \App\Http\Controllers\GelAdmin\Traits\HasAdminEntity;

    public function index()
    {
        $cabinet = $this->getAdminEntity();
        // Roles spécifiques à l'entreprise
        $roles = Role::where('guard_name', 'web')->get();
        return view('gel-admin.team.roles', compact('roles', 'cabinet'));
    }

    public function create()
    {
        $cabinet = $this->getAdminEntity();
        $permissions = Permission::all()->groupBy('module');
        return view('gel-admin.team.role-create', compact('permissions', 'cabinet'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'array'
        ]);

        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'web',
            'description' => $request->description,
        ]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return redirect()->route('gel-admin.team.roles.index')->with('success', 'Rôle créé avec succès.');
    }

    public function edit($id)
    {
        $cabinet = $this->getAdminEntity();
        $role = Role::findOrFail($id);
        $permissions = Permission::all()->groupBy('module');
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('gel-admin.team.role-edit', compact('role', 'permissions', 'rolePermissions', 'cabinet'));
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
            'permissions' => 'array'
        ]);

        $role->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        } else {
            $role->syncPermissions([]);
        }

        return redirect()->route('gel-admin.team.roles.index')->with('success', 'Rôle mis à jour.');
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();

        return redirect()->route('gel-admin.team.roles.index')->with('success', 'Rôle supprimé.');
    }
}
