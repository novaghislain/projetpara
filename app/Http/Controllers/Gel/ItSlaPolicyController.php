<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\ItSlaPolicy;
use App\Services\AuditTrailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ItSlaPolicyController extends Controller
{
    public function index(): View
    {
        $policies = ItSlaPolicy::latest()->paginate(20);
        return view('app', ['page' => 'gel-it-sla-policies', 'props' => compact('policies')]);
    }

    public function create(): View
    {
        return view('app', ['page' => 'gel-it-sla-policies-form']);
    }

    public function show(ItSlaPolicy $policy): View
    {
        return view('app', ['page' => 'gel-it-sla-policies-show', 'props' => compact('policy')]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'priority' => 'required|in:low,medium,high,critical',
            'first_response_hours' => 'required|integer|min:1',
            'resolution_hours' => 'required|integer|min:1',
            'is_default' => 'boolean',
        ]);

        if ($validated['is_default'] ?? false) {
            ItSlaPolicy::where('is_default', true)->update(['is_default' => false]);
        }

        $policy = ItSlaPolicy::create($validated);
        AuditTrailService::log($policy, 'created', null, $validated, 'Politique SLA créée');

        return redirect()->route('gel.it-sla-policies.index')->with('success', 'Politique SLA créée.');
    }

    public function update(Request $request, ItSlaPolicy $policy): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'priority' => 'required|in:low,medium,high,critical',
            'first_response_hours' => 'required|integer|min:1',
            'resolution_hours' => 'required|integer|min:1',
            'is_default' => 'boolean',
        ]);

        if ($validated['is_default'] ?? false) {
            ItSlaPolicy::where('is_default', true)->where('id', '!=', $policy->id)->update(['is_default' => false]);
        }

        $old = $policy->getAttributes();
        $policy->update($validated);
        AuditTrailService::log($policy, 'updated', $old, $policy->getAttributes(), 'Politique SLA mise à jour');

        return redirect()->route('gel.it-sla-policies.index')->with('success', 'Politique SLA mise à jour.');
    }

    public function destroy(ItSlaPolicy $policy): RedirectResponse
    {
        $old = $policy->getAttributes();
        $policy->delete();
        AuditTrailService::log($policy, 'deleted', $old, null, 'Politique SLA supprimée');
        return redirect()->route('gel.it-sla-policies.index')->with('success', 'Politique SLA supprimée.');
    }
}
