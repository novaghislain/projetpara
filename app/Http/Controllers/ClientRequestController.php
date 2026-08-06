<?php

namespace App\Http\Controllers;

use App\Models\ClientRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Company\BaseCompanyController;

class ClientRequestController extends BaseCompanyController
{
    private function authorizeAdmin(): void
    {
        $user = Auth::user();
        if (!$user->isCompanyAdmin()) {
            abort(403, 'Accès réservé à l\'administrateur.');
        }
    }

    public function index(Request $request)
    {
        $this->authorizeAdmin();
        $clientId = $this->getClientId();

        $query = ClientRequest::where('client_id', $clientId)->with(['consumer:id,name,email', 'assignee:id,name']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json([
            'requests' => $query->latest()->get()
        ]);
    }

    public function show($id)
    {
        $this->authorizeAdmin();
        $clientId = $this->getClientId();

        $clientRequest = ClientRequest::where('client_id', $clientId)
            ->with(['consumer:id,name,email', 'assignee:id,name'])
            ->findOrFail($id);

        return response()->json($clientRequest);
    }

    public function updateStatus(Request $request, $id)
    {
        $this->authorizeAdmin();
        $clientId = $this->getClientId();

        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,validated,rejected',
            'rejection_reason' => 'required_if:status,rejected|string|nullable'
        ]);

        $clientRequest = ClientRequest::where('client_id', $clientId)->findOrFail($id);
        $clientRequest->status = $validated['status'];
        if ($validated['status'] === 'rejected') {
            $clientRequest->rejection_reason = $validated['rejection_reason'];
        }
        $clientRequest->save();

        // Notification (optionnel)

        return response()->json([
            'message' => 'Statut mis à jour.',
            'request' => $clientRequest
        ]);
    }

    public function assign(Request $request, $id)
    {
        $this->authorizeAdmin();
        $clientId = $this->getClientId();

        $validated = $request->validate([
            'assigned_to' => 'nullable|exists:users,id'
        ]);

        $clientRequest = ClientRequest::where('client_id', $clientId)->findOrFail($id);
        
        if ($validated['assigned_to']) {
            // Check if assignee belongs to the same client
            $assignee = User::where('client_id', $clientId)->findOrFail($validated['assigned_to']);
            $clientRequest->assigned_to = $assignee->id;
            $clientRequest->status = 'in_progress';
        } else {
            $clientRequest->assigned_to = null;
        }

        $clientRequest->save();

        return response()->json([
            'message' => 'Assignation mise à jour.',
            'request' => $clientRequest->load('assignee:id,name')
        ]);
    }
}
