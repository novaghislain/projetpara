<?php

namespace App\Http\Controllers\GelSecretary;

use App\Http\Controllers\Controller;
use App\Models\Gel\Client;
use App\Models\User;
use App\Models\Gel\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CabinetController extends Controller
{
    public function portefeuille()
    {
        $user = Auth::user();
        $clients = Client::where('created_by', $user->cabinet_id ?? $user->id)
                        ->orWhereIn('id', $user->userClients()->pluck('client_id'))
                        ->orderBy('nom_entreprise')
                        ->get();

        // Calculate health, pending docs, etc for each client
        foreach ($clients as $c) {
            $c->health = rand(60, 100); // placeholder
            $c->pending_docs = \App\Models\Document::where('client_id', $c->id)->where('workflow_step', 'recu')->count();
            $c->pending_tasks = Task::where('client_id', $c->id)->where('statut', '!=', 'termine')->count();
        }

        return view('gel-secretary.cabinet.portefeuille', compact('clients'));
    }

    public function collaborateurs()
    {
        // Simple mock for collaborateurs for MVP
        $collaborateurs = [
            (object)['id' => 1, 'name' => 'Jean Assistant', 'email' => 'jean@assistant.com', 'clients' => 2],
            (object)['id' => 2, 'name' => 'Marie Comptable', 'email' => 'marie@comptable.com', 'clients' => 5],
        ];

        return view('gel-secretary.cabinet.collaborateurs', compact('collaborateurs'));
    }

    public function taches()
    {
        $user = Auth::user();
        $clientIds = $user->userClients()->pluck('client_id');
        
        $taches = Task::whereIn('client_id', $clientIds)
                      ->with('client')
                      ->orderBy('date_echeance', 'asc')
                      ->get();

        return view('gel-secretary.cabinet.taches', compact('taches'));
    }
}
