<?php

namespace App\Http\Controllers\GelAccountant;

use App\Http\Controllers\Controller;
use App\Models\Gel\Workflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkflowsController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $cabinetId = $user->cabinet_id;

        $query = Workflow::where('cabinet_id', $cabinetId);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $workflows = $query->with('client:id,nom_entreprise')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'actifs' => Workflow::where('cabinet_id', $cabinetId)->where('actif', true)->count(),
            'executes_mois' => Workflow::where('cabinet_id', $cabinetId)
                ->where('actif', true)
                ->count(),
        ];

        return view('gel-accountant.workflows.index', compact('workflows', 'stats'));
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'client_id' => 'nullable|exists:gel_clients,id',
            'conditions' => 'nullable|json',
            'actions' => 'nullable|json',
            'frequence' => 'required|in:immediate,quotidienne,hebdomadaire,personnalisee',
        ]);

        $validated['cabinet_id'] = $user->cabinet_id;
        $validated['actif'] = true;

        Workflow::create($validated);

        return redirect()->route('gel-accountant.workflows')
            ->with('success', 'Workflow créé avec succès.');
    }

    public function toggle($id)
    {
        $workflow = Workflow::findOrFail($id);
        $workflow->actif = !$workflow->actif;
        $workflow->save();

        return redirect()->route('gel-accountant.workflows')
            ->with('success', 'Statut du workflow mis à jour.');
    }

    public function destroy($id)
    {
        $workflow = Workflow::findOrFail($id);
        $workflow->delete();

        return redirect()->route('gel-accountant.workflows')
            ->with('success', 'Workflow supprimé.');
    }
}
