<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\ClientRequest;

class PublicContactController extends Controller
{
    public function showForm($slug)
    {
        $client = Client::where('slug', $slug)->firstOrFail();
        return view('public.contact', compact('client'));
    }

    public function submitForm(Request $request, $slug)
    {
        $client = Client::where('slug', $slug)->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'type' => 'required|string|in:general,support,quote,other',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $clientRequest = ClientRequest::create([
            'client_id' => $client->id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'type' => $request->type,
            'title' => $request->title,
            'description' => $request->description,
            'status' => 'pending',
        ]);

        \App\Events\ClientRequestCreated::dispatch($clientRequest);

        return back()->with('success', 'Votre demande a été envoyée avec succès.');
    }
}
