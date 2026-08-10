<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\CompanyCrmContact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CrmClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $contacts = CompanyCrmContact::orderBy('created_at', 'desc')->get();
        return response()->json($contacts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'nullable|integer', // might refer to cabinet client
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'tags' => 'nullable|array',
        ]);

        $validated['created_by'] = Auth::id();

        $contact = CompanyCrmContact::create($validated);

        return response()->json($contact, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(CompanyCrmContact $contact)
    {
        return response()->json($contact);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CompanyCrmContact $contact)
    {
        $validated = $request->validate([
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'tags' => 'nullable|array',
        ]);

        $contact->update($validated);

        return response()->json($contact);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CompanyCrmContact $contact)
    {
        $contact->delete();

        return response()->json(null, 204);
    }
}
