<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Helper to get client by slug
     */
    private function getClientBySlug($slug)
    {
        $client = Client::where('portal_slug', $slug)->firstOrFail();
        if (!$client->portal_active) {
            abort(403, 'Le portail de cette entreprise est actuellement désactivé.');
        }
        return $client;
    }

    /**
     * View the profile
     */
    public function show($slug)
    {
        $client = $this->getClientBySlug($slug);
        $user = Auth::guard('portal')->user();
        
        // Fetch any pending corrections for this user and client
        $pendingCorrections = \App\Models\PortalContactCorrection::where('portal_contact_id', $user->id)
            ->where('client_id', $client->id)
            ->where('status', 'pending')
            ->get();

        return view('portal.profile.show', compact('client', 'slug', 'user', 'pendingCorrections'));
    }

    /**
     * Update the profile
     */
    public function update(Request $request, $slug)
    {
        $client = $this->getClientBySlug($slug);
        $user = Auth::guard('portal')->user();

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
        ]);

        return back()->with('success', 'Vos informations ont été mises à jour avec succès.');
    }

    /**
     * Handle the correction acceptance or rejection
     */
    public function handleCorrection(Request $request, $slug, $correctionId)
    {
        $client = $this->getClientBySlug($slug);
        $user = Auth::guard('portal')->user();

        $correction = \App\Models\PortalContactCorrection::where('id', $correctionId)
            ->where('portal_contact_id', $user->id)
            ->where('client_id', $client->id)
            ->where('status', 'pending')
            ->firstOrFail();

        $action = $request->input('action'); // 'accept' or 'reject'

        if ($action === 'accept') {
            // Update the user's field
            $user->update([
                $correction->field_name => $correction->new_value
            ]);
            $correction->update(['status' => 'accepted']);
            $message = "Correction acceptée et appliquée avec succès.";
        } else {
            $correction->update(['status' => 'rejected']);
            $message = "Correction refusée.";
        }

        return back()->with('success', $message);
    }
}
