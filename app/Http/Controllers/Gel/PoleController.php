<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\Pole;
use Illuminate\Http\Request;

class PoleController extends Controller
{
    /**
     * Page liste des pôles.
     */
    public function index()
    {
        return view('app', ['page' => 'gel-poles']);
    }

    /**
     * Créer un pôle.
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
     */
    public function show($id)
    {
        return view('app', [
            'page' => 'gel-poles-show',
            'poleId' => $id,
        ]);
    }

    /**
     * Mettre à jour un pôle.
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
     * Supprimer un pôle.
     */
    public function destroy($id)
    {
        $pole = Pole::findOrFail($id);
        $pole->delete();

        return redirect()->route('poles.index')
            ->with('success', 'Pôle supprimé avec succès');
    }

    /**
     * API: Liste de tous les pôles.
     */
    public function listAll()
    {
        return response()->json(
            Pole::withCount(['users', 'missions', 'clients'])->latest()->get()
        );
    }

    /**
     * API: Créer un pôle.
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
     * API: Mettre à jour un pôle.
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
     * API: Supprimer un pôle.
     */
    public function apiDestroy($id)
    {
        $pole = Pole::findOrFail($id);
        $pole->delete();

        return response()->json(['message' => 'Pôle supprimé avec succès']);
    }
}
