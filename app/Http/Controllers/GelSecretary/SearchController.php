<?php

namespace App\Http\Controllers\GelSecretary;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $q = $request->input('q');
        if (!$q || strlen($q) < 2) {
            return response()->json([]);
        }

        $user = Auth::user();
        $results = collect();

        // 1. Rechercher des clients (Entreprises)
        $clientIds = $user->userClients()->pluck('client_id')->toArray();
        $query = \App\Models\Gel\Client::query();
        if ($user->cabinet_id) {
            $query->where('cabinet_id', $user->cabinet_id)->orWhereIn('id', $clientIds);
        } else {
            $query->whereIn('id', $clientIds);
        }
        
        $clients = (clone $query)->where(function($qq) use ($q) {
            $qq->where('nom_entreprise', 'like', "%{$q}%")
               ->orWhere('email', 'like', "%{$q}%")
               ->orWhere('telephone', 'like', "%{$q}%")
               ->orWhere('ville', 'like', "%{$q}%");
        })->take(3)->get();

        foreach($clients as $c) {
            $results->push([
                'category' => 'Entreprises',
                'title' => $c->nom_entreprise,
                'subtitle' => $c->ville ?? '—',
                'icon' => 'fas fa-building text-primary',
                'url' => route('gel-secretary.clients.show', $c->id)
            ]);
        }

        // 2. Rechercher des Tâches
        $tasksQuery = \App\Models\Gel\Task::where('cabinet_id', $user->cabinet_id);
        $tasks = $tasksQuery->where(function($qq) use ($q) {
            $qq->where('titre', 'like', "%{$q}%")
               ->orWhere('description', 'like', "%{$q}%");
        })->take(3)->get();

        foreach($tasks as $t) {
            $results->push([
                'category' => 'Tâches',
                'title' => $t->titre,
                'subtitle' => 'Statut: ' . str_replace('_', ' ', $t->statut),
                'icon' => 'fas fa-check-square text-success',
                'url' => route('gel-secretary.dashboard') // fallback
            ]);
        }

        return response()->json($results->groupBy('category'));
    }
}
