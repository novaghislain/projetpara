<?php

namespace App\Http\Controllers\GelSecretary;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\ClientFolder;
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

        // 3. Rechercher des Documents (noms de fichiers réels stockés).
        //    P2 : interroge le nom stocké en base (name / original_name / description),
        //    insensible à la casse et aux accents (collation utf8mb4_unicode_ci),
        //    sur TOUS les dossiers du client actif — aucun filtre restrictif par défaut.
        $activeClientId = session('active_client_id') ?? $user->active_client_id;
        
        $documents = Document::active();
        
        if ($user->isAutonomousSecretary()) {
            $documents->where('uploaded_by', $user->id);
        } else {
            $documents->when($activeClientId, function ($query) use ($activeClientId) {
                return $query->where('client_id', $activeClientId);
            });
        }
        
        $documents = $documents->where(function ($qq) use ($q) {
                $searchTerm = '%' . strtolower($q) . '%';
                $qq->whereRaw('LOWER(name) LIKE ?', [$searchTerm])
                   ->orWhereRaw('LOWER(original_name) LIKE ?', [$searchTerm])
                   ->orWhereRaw('LOWER(description) LIKE ?', [$searchTerm])
                   ->orWhereRaw('LOWER(category) LIKE ?', [$searchTerm])
                   ->orWhereRaw('LOWER(reference_number) LIKE ?', [$searchTerm]);
            })
            ->with('folder:id,name')
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        foreach ($documents as $doc) {
            $folder = $doc->folder;
            $results->push([
                'category' => 'Documents',
                'title' => $doc->name,
                'subtitle' => $folder
                    ? ($folder->path ?: $folder->name)
                    : 'Aucun dossier',
                'icon' => 'fas fa-file-alt text-primary',
                'url' => $doc->folder_id
                    ? route('gel-secretary.documents.folder', $doc->folder_id)
                    : route('gel-secretary.documents.index'),
            ]);
        }

        return response()->json($results->groupBy('category'));
    }
}
