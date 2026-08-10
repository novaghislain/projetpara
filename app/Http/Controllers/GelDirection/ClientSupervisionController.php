<?php

namespace App\Http\Controllers\GelDirection;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;

class ClientSupervisionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Client::where('cabinet_id', $user->cabinet_id);
        
        if ($request->search) {
            $query->where('nom_entreprise', 'like', '%' . $request->search . '%')
                  ->orWhere('email_contact', 'like', '%' . $request->search . '%');
        }
        
        $clients = $query->paginate(15);
        
        return view('gel-direction.clients.index', compact('clients'));
    }
    
    public function show(Client $client)
    {
        // Vérifier l'appartenance au cabinet
        $user = Auth::user();
        if ($client->cabinet_id !== $user->cabinet_id) {
            abort(403);
        }
        
        // Simuler des statistiques liées au client pour la direction
        $caGenere = 15400; // Fake CA
        $tasksCount = \App\Models\Task::where('client_id', $client->id)->count();
        
        return view('gel-direction.clients.show', compact('client', 'caGenere', 'tasksCount'));
    }
}
