<?php

namespace App\Http\Controllers\GelAdmin\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class ProfileController extends Controller
{
    use \App\Http\Controllers\GelAdmin\Traits\HasAdminEntity;

    public function index()
    {
        $cabinet = $this->getAdminEntity();
        $type = $this->getAdminEntityType();
        
        // Normalize fields for the view
        $profile = [
            'nom' => $type === 'client' ? $cabinet->company_name : $cabinet->nom,
            'email' => $cabinet->email,
            'telephone' => $type === 'client' ? $cabinet->phone : $cabinet->telephone,
            'adresse' => $type === 'client' ? $cabinet->address : $cabinet->adresse,
            'ville' => $type === 'client' ? $cabinet->city : $cabinet->ville,
            'pays' => $type === 'client' ? $cabinet->country : $cabinet->pays,
            'site_web' => $type === 'client' ? $cabinet->website : $cabinet->site_web,
            'ifu' => $cabinet->ifu,
            'rccm' => $cabinet->rccm,
            'logo' => $type === 'client' ? null : $cabinet->logo, // Client doesn't have logo in this schema usually
        ];
        
        return view('gel-admin.settings.profile', compact('cabinet', 'type', 'profile'));
    }

    public function update(Request $request)
    {
        $cabinet = $this->getAdminEntity();
        $type = $this->getAdminEntityType();
        
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'telephone' => 'nullable|string|max:20',
            'email' => 'required|email|max:255',
            'adresse' => 'nullable|string|max:255',
            'ville' => 'nullable|string|max:100',
            'pays' => 'nullable|string|max:100',
            'site_web' => 'nullable|string|max:255',
            'ifu' => 'nullable|string|max:100',
            'rccm' => 'nullable|string|max:100',
            'logo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('logo') && $type !== 'client') {
            if ($cabinet->logo) {
                Storage::disk('public')->delete($cabinet->logo);
            }
            $validated['logo'] = $request->file('logo')->store('cabinets/logos', 'public');
        }

        if ($type === 'client') {
            $cabinet->update([
                'company_name' => $validated['nom'],
                'phone' => $validated['telephone'],
                'email' => $validated['email'],
                'address' => $validated['adresse'],
                'city' => $validated['ville'],
                'country' => $validated['pays'],
                'website' => $validated['site_web'],
                'ifu' => $validated['ifu'],
                'rccm' => $validated['rccm'],
            ]);
        } else {
            $cabinet->update($validated);
        }

        // Audit Log
        if (class_exists(\App\Models\AuditLog::class)) {
            \App\Models\AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'updated_profile',
                'module' => 'Admin',
                'description' => 'Profil du cabinet mis à jour.',
                'ip_address' => $request->ip(),
            ]);
        }

        return redirect()->route('gel-admin.profile.index')->with('success', 'Profil mis à jour avec succès.');
    }

    public function transferOwnership(Request $request)
    {
        $cabinet = $this->getAdminEntity();
        $type = $this->getAdminEntityType();
        $foreignKey = $type . '_id';
        
        $validated = $request->validate([
            'new_owner_id' => 'required|exists:users,id'
        ]);

        $newOwner = User::find($validated['new_owner_id']);
        if ($newOwner->{$foreignKey} !== Auth::user()->{$foreignKey}) {
            return back()->with('error', 'Le nouveau propriétaire doit faire partie de votre équipe.');
        }

        $currentOwner = Auth::user();
        
        $currentOwner->is_company_admin = false;
        $currentOwner->save();

        $newOwner->is_company_admin = true;
        $newOwner->save();

        return redirect()->route('gel-admin.dashboard')->with('success', 'Propriété transférée avec succès.');
    }
}
