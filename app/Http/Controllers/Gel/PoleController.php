<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\Pole;
use Illuminate\Http\Request;

class PoleController extends Controller
{
    /**
     * Contrôleur de gestion des pôles (départements) du cabinet GEL.
     * Permet de gérer les pôles d'activité avec leurs métriques
     * (nombre d'utilisateurs, missions, clients associés).
     */

    /**
     * Page liste des pôles.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('app', ['page' => 'gel-poles']);
    }

    /**
     * Crée un nouveau pôle (web).
     *
     * @param Request $request La requête HTTP avec les données du pôle
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:poles,slug',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $pole = Pole::create($validated);

        return redirect()->route('poles.show', $pole->id)
            ->with('success', 'Pôle créé avec succès');
    }

    /**
     * Page détail d'un pôle.
     *
     * @param int $id L'identifiant du pôle
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        return view('app', [
            'page' => 'gel-poles-show',
            'poleId' => $id,
        ]);
    }

    /**
     * Met à jour un pôle (web).
     *
     * @param Request $request La requête HTTP avec les données mises à jour
     * @param int $id L'identifiant du pôle
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $pole = Pole::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:poles,slug,' . $id,
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $pole->update($validated);

        return redirect()->route('poles.show', $pole->id)
            ->with('success', 'Pôle mis à jour avec succès');
    }

    /**
     * Supprime un pôle (web).
     *
     * @param int $id L'identifiant du pôle
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $pole = Pole::findOrFail($id);
        $pole->delete();

        return redirect()->route('poles.index')
            ->with('success', 'Pôle supprimé avec succès');
    }

    /**
     * API : Liste tous les pôles avec les compteurs associés.
     *
     * @return \Illuminate\Http\JsonResponse La liste des pôles
     */
    public function listAll()
    {
        return response()->json(
            Pole::withCount(['users', 'missions', 'clients'])->latest()->get()
        );
    }

    /**
     * API : Crée un nouveau pôle.
     *
     * @param Request $request La requête HTTP avec les données du pôle
     * @return \Illuminate\Http\JsonResponse Le pôle créé
     */
    public function apiStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:poles,slug',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $pole = Pole::create($validated);

        return response()->json($pole, 201);
    }

    /**
     * API : Met à jour un pôle.
     *
     * @param Request $request La requête HTTP avec les données mises à jour
     * @param int $id L'identifiant du pôle
     * @return \Illuminate\Http\JsonResponse Le pôle mis à jour
     */
    public function apiUpdate(Request $request, $id)
    {
        $pole = Pole::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:poles,slug,' . $id,
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $pole->update($validated);

        return response()->json($pole);
    }

    /**
     * API : Supprime un pôle.
     *
     * @param int $id L'identifiant du pôle
     * @return \Illuminate\Http\JsonResponse Message de confirmation
     */
    public function apiDestroy($id)
    {
        $pole = Pole::findOrFail($id);
        $pole->delete();

        return response()->json(['message' => 'Pôle supprimé avec succès']);
    }
}
