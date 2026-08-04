<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Contrôleur de gestion des services proposés par le cabinet GEL.
     * Permet de gérer le catalogue de services avec leurs métadonnées
     * (icône, couleur, catégorie) et le suivi des clients associés.
     */

    /**
     * Page liste des services.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('app', ['page' => 'gel-services']);
    }

    /**
     * Page détail d'un service avec ses clients associés.
     *
     * @param int $id L'identifiant du service
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        return view('app', [
            'page' => 'gel-services-show',
            'serviceId' => $id,
        ]);
    }

    // ─── API ────────────────────────────────────────────────────

    /**
     * API : Liste tous les services actifs avec le compteur de clients associés.
     *
     * @return \Illuminate\Http\JsonResponse La liste des services
     */
    public function listAll()
    {
        return response()->json(
            Service::withCount('clientServices')->active()->latest()->get()
        );
    }

    /**
     * API : Détail d'un service avec ses clients associés.
     *
     * @param int $id L'identifiant du service
     * @return \Illuminate\Http\JsonResponse Le service avec ses relations
     */
    public function getService($id)
    {
        $service = Service::with(['clientServices' => fn($q) => $q->with('client')])
            ->findOrFail($id);

        return response()->json($service);
    }

    /**
     * API : Crée un nouveau service.
     *
     * @param Request $request La requête HTTP avec les données du service
     * @return \Illuminate\Http\JsonResponse Le service créé
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:services,slug',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:20',
            'category' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        $service = Service::create($validated);

        return response()->json($service, 201);
    }

    /**
     * API : Met à jour un service existant.
     *
     * @param Request $request La requête HTTP avec les données mises à jour
     * @param int $id L'identifiant du service
     * @return \Illuminate\Http\JsonResponse Le service mis à jour
     */
    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:services,slug,' . $id,
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:20',
            'category' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        $service->update($validated);

        return response()->json($service);
    }

    /**
     * API : Supprime un service.
     *
     * @param int $id L'identifiant du service
     * @return \Illuminate\Http\JsonResponse Message de confirmation
     */
    public function destroy($id)
    {
        $service = Service::findOrFail($id);
        $service->delete();

        return response()->json(['message' => 'Service supprimé']);
    }
}
