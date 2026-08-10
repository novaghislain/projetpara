<?php

namespace App\Http\Controllers\GelSuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GelSuperAdmin\SubscriptionPlan;
use Illuminate\Support\Facades\Auth;
use App\Models\AuditTrail;

class PlanController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlan::orderBy('price', 'asc')->get();
        return view('gel-super-admin.plans.index', compact('plans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'max_users' => 'nullable|integer|min:1',
            'ia_quota' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'profile_type' => 'required|in:entreprise,secretaire_independant,comptable_independant,gel_pool'
        ]);

        $plan = SubscriptionPlan::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'max_users' => $validated['max_users'],
            'ia_quota' => $validated['ia_quota'],
            'profile_type' => $validated['profile_type'],
            'is_active' => $request->has('is_active'),
        ]);

        AuditTrail::create([
            'user_id' => Auth::id(),
            'event' => 'CREATE_PLAN',
            'description' => "Création du forfait {$plan->name}.",
            'ip_address' => request()->ip(),
            'auditable_type' => SubscriptionPlan::class,
            'auditable_id' => $plan->id
        ]);

        return back()->with('success', 'Le forfait a été créé avec succès.');
    }

    public function update(Request $request, $id)
    {
        $plan = SubscriptionPlan::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'max_users' => 'nullable|integer|min:1',
            'ia_quota' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'profile_type' => 'required|in:entreprise,secretaire_independant,comptable_independant,gel_pool'
        ]);

        $plan->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'max_users' => $validated['max_users'],
            'ia_quota' => $validated['ia_quota'],
            'profile_type' => $validated['profile_type'],
            'is_active' => $request->has('is_active'),
        ]);

        AuditTrail::create([
            'user_id' => Auth::id(),
            'event' => 'UPDATE_PLAN',
            'description' => "Modification du forfait {$plan->name}.",
            'ip_address' => request()->ip(),
            'auditable_type' => SubscriptionPlan::class,
            'auditable_id' => $plan->id
        ]);

        return back()->with('success', 'Le forfait a été mis à jour avec succès.');
    }

    public function destroy($id)
    {
        $plan = SubscriptionPlan::findOrFail($id);
        $name = $plan->name;
        $plan->delete();

        AuditTrail::create([
            'user_id' => Auth::id(),
            'event' => 'DELETE_PLAN',
            'description' => "Suppression du forfait {$name}.",
            'ip_address' => request()->ip(),
            'auditable_type' => SubscriptionPlan::class,
            'auditable_id' => $id
        ]);

        return back()->with('success', 'Le forfait a été supprimé.');
    }
}
