<?php

namespace App\Http\Controllers\Gel\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cabinet;
use App\Models\ModuleCabinet;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CabinetController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:admin.config');
    }

    /**
     * Affiche la page de configuration du cabinet.
     */
    public function index()
    {
        $cabinetId = Auth::user()->cabinet_id;

        $cabinet = Cabinet::find($cabinetId);

        if (!$cabinet) {
            // Créer un cabinet par défaut si l'utilisateur en a un ID mais n'existe pas
            $cabinet = Cabinet::create([
                'nom' => 'Mon Cabinet',
                'slug' => 'mon-cabinet-' . Auth::id(),
                'email' => Auth::user()->email,
                'is_active' => true,
            ]);
        }

        $modules = ModuleCabinet::where('cabinet_id', $cabinet->id)->get();

        return view('gel.admin.cabinet', compact('cabinet', 'modules'));
    }

    /**
     * Met à jour les informations du cabinet.
     */
    public function update(Request $request)
    {
        $cabinetId = Auth::user()->cabinet_id;
        $cabinet = Cabinet::findOrFail($cabinetId);

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:50',
            'adresse' => 'nullable|string',
            'ville' => 'nullable|string|max:100',
            'pays' => 'nullable|string|max:100',
            'site_web' => 'nullable|url|max:255',
            'description' => 'nullable|string',
            'ifu' => 'nullable|string|max:100',
            'rccm' => 'nullable|string|max:100',
        ]);

        if ($request->hasFile('logo')) {
            $request->validate(['logo' => 'image|mimes:png,jpg,jpeg,webp|max:2048']);
            $path = $request->file('logo')->store('cabinets/logos', 'public');
            $validated['logo'] = $path;

            // Supprimer l'ancien logo
            if ($cabinet->logo && Storage::disk('public')->exists($cabinet->logo)) {
                Storage::disk('public')->delete($cabinet->logo);
            }
        }

        $cabinet->update($validated);

        AuditTrail::create([
            'user_id' => Auth::id(),
            'event' => 'cabinet_update',
            'auditable_type' => Cabinet::class,
            'auditable_id' => $cabinet->id,
            'description' => 'Mise à jour du cabinet : ' . $cabinet->nom,
            'old_values' => $cabinet->getOriginal(),
            'new_values' => $validated,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'cabinet' => $cabinet->fresh(),
            'message' => 'Cabinet mis à jour avec succès.',
        ]);
    }

    /**
     * Active/désactive un module du cabinet.
     */
    public function toggleModule(Request $request, $moduleId)
    {
        $cabinetId = Auth::user()->cabinet_id;
        $module = ModuleCabinet::where('cabinet_id', $cabinetId)
            ->findOrFail($moduleId);

        $module->update(['is_active' => !$module->is_active]);

        $status = $module->is_active ? 'activé' : 'désactivé';

        AuditTrail::create([
            'user_id' => Auth::id(),
            'event' => 'cabinet_module_toggle',
            'auditable_type' => ModuleCabinet::class,
            'auditable_id' => $moduleId,
            'description' => "Module {$module->module} {$status}",
        ]);

        return response()->json([
            'success' => true,
            'module' => $module->fresh(),
            'message' => "Module {$status} avec succès.",
        ]);
    }

    /**
     * Met à jour les limites et configuration du cabinet.
     */
    public function updateLimits(Request $request)
    {
        $cabinetId = Auth::user()->cabinet_id;
        $cabinet = Cabinet::findOrFail($cabinetId);

        $validated = $request->validate([
            'limits.max_users' => 'nullable|integer|min:1|max:10000',
            'limits.max_storage_mb' => 'nullable|integer|min:1|max:100000',
            'limits.max_clients' => 'nullable|integer|min:1|max:100000',
            'config.locale' => 'nullable|string|max:10',
            'config.timezone' => 'nullable|string|max:50',
            'config.date_format' => 'nullable|string|max:20',
        ]);

        $limits = array_merge($cabinet->limits ?? [], $validated['limits'] ?? []);
        $config = array_merge($cabinet->config ?? [], $validated['config'] ?? []);

        $cabinet->update([
            'limits' => $limits,
            'config' => $config,
        ]);

        return response()->json([
            'success' => true,
            'cabinet' => $cabinet->fresh(),
            'message' => 'Configuration mise à jour.',
        ]);
    }
}
