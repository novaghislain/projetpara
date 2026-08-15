<?php

namespace App\Http\Controllers\GelAccountant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PortalContactCorrection;
use App\Models\PortalContact;
use Illuminate\Support\Facades\Auth;
use App\Models\Client;

class ClientCorrectionController extends Controller
{
    /**
     * Propose a correction to a portal contact
     */
    public function propose(Request $request, $contactId)
    {
                $clientId = session('active_client_id') ?? session('current_client_id');
        $client = Client::findOrFail($clientId);

        $contact = PortalContact::findOrFail($contactId);

        // Ensure contact is linked to client
        if (!$contact->clients()->where('client_id', $client->id)->exists()) {
            abort(403);
        }

        $request->validate([
            'field_name' => 'required|string|in:first_name,last_name,phone',
            'new_value' => 'required|string',
        ]);

        PortalContactCorrection::create([
            'portal_contact_id' => $contact->id,
            'client_id' => $client->id,
            'field_name' => $request->field_name,
            'old_value' => $contact->{$request->field_name},
            'new_value' => $request->new_value,
            'status' => 'pending',
            'proposed_by_user_id' => $user->id,
        ]);

        return back()->with('success', 'La proposition de correction a été envoyée au client.');
    }
}
