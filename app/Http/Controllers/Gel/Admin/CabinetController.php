<?php

namespace App\Http\Controllers\Gel\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cabinet;
use App\Models\ModuleCabinet;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Contrôleur de gestion des cabinets d'expertise comptable.
 * Permet la configuration des informations du cabinet, l'activation/désactivation
 * des modules et la mise à jour des limites et configurations générales.
 */
class CabinetController extends Controller
{
    /**
     * Constructeur : applique le middleware de permission pour l'administration.
     */
    public function __construct()
    {
        $this->middleware('permission:admin.config');
    }

    /**
     * Affiche la page de configuration du cabinet.
     * Récupère ou crée le cabinet par défaut et liste les modules disponibles.
     *
     * @return \Illuminate\View\View
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

        // Récupérer tous les modules associés à ce cabinet
        $modules = ModuleCabinet::where('cabinet_id', $cabinet->id)->get();

        return view('gel.admin.cabinet', compact('cabinet', 'modules'));
    }

    /**
     * Met à jour les informations du cabinet.
     * Valide les champs, gère le téléchargement du logo et enregistre une trace d'audit.
     *
     * @param Request $request La requête HTTP contenant les données du cabinet
     * @return \Illuminate\Http\JsonResponse
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

        // Gestion du logo : validation et stockage
        if ($request->hasFile('logo')) {
            $request->validate(['logo' => 'image|mimes:png,jpg,jpeg,webp|max:2048']);
            $path = $request->file('logo')->store('cabinets/logos', 'public');
            $validated['logo'] = $path;

            // Supprimer l'ancien logo du stockage pour libérer de l'espace
            if ($cabinet->logo && Storage::disk('public')->exists($cabinet->logo)) {
                Storage::disk('public')->delete($cabinet->logo);
            }
        }

        $cabinet->update($validated);

        // Enregistrer une trace d'audit pour la mise à jour
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
     * Active ou désactive un module du cabinet.
     * Inverse l'état actuel du module et enregistre une trace d'audit.
     *
     * @param Request $request La requête HTTP
     * @param int $moduleId L'identifiant du module à basculer
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleModule(Request $request, $moduleId)
    {
        $cabinetId = Auth::user()->cabinet_id;
        $module = ModuleCabinet::where('cabinet_id', $cabinetId)
            ->findOrFail($moduleId);

        // Inverser l'état d'activation du module
        $module->update(['is_active' => !$module->is_active]);

        $status = $module->is_active ? 'activé' : 'désactivé';

        // Enregistrer la modification dans les traces d'audit
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
     * Met à jour les limites et la configuration générale du cabinet.
     * Fusionne les nouvelles valeurs avec les existantes.
     *
     * @param Request $request La requête HTTP contenant limits et/ou config
     * @return \Illuminate\Http\JsonResponse
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

        // Fusionner les nouvelles limites/config avec les existantes pour ne pas les écraser
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
