<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Page liste des services.
     */
    public function index()
    {
        return view('app', ['page' => 'gel-services']);
    }

    /**
     * Page détail d'un service.
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
     * API: Liste de tous les services.
     */
    public function listAll()
    {
        return response()->json(
            Service::withCount('clientServices')->active()->latest()->get()
        );
    }

    /**
     * API: Détail d'un service.
     */
    public function getService($id)
    {
        $service = Service::with(['clientServices' => fn($q) => $q->with('client')])
            ->findOrFail($id);

        return response()->json($service);
    }

    /**
     * API: Créer un service.
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
     * API: Mettre à jour un service.
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
     * API: Supprimer un service.
     */
    public function destroy($id)
    {
        $service = Service::findOrFail($id);
        $service->delete();

        return response()->json(['message' => 'Service supprimé']);
    }
}
