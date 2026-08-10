@extends('layouts.gel-accountant')

@section('title', 'Mes Clients - Comptable Indépendant')

@section('content')
<style>
  @keyframes fadeUp {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
  }
  .animate-fade { animation: fadeUp 0.4s ease-out forwards; opacity: 0; }
  .delay-1 { animation-delay: 0.05s; }
  
  .pro-panel {
    background: white; border-radius: 8px; border: 1px solid var(--gel-border);
    box-shadow: 0 1px 3px rgba(0,0,0,0.02); display: flex; flex-direction: column;
  }
  .panel-body { padding: 20px; }
  .table th { font-weight: 600; color: var(--gel-text-secondary); font-size: 13px; }
  .table td { vertical-align: middle; font-size: 14px; }
</style>

<div class="gel-page-header animate-fade" style="border-bottom: 1px solid var(--gel-border); padding-bottom: 12px; margin-bottom: 24px; display:flex; justify-content:space-between; align-items:flex-end;">
    <div>
        <h1 class="gel-page-title" style="font-size: 18px; font-weight: 700; color: var(--gel-text-primary); margin-bottom: 4px;">Mes Clients Gérés</h1>
        <p class="gel-page-subtitle" style="font-size: 12px; color: var(--gel-text-secondary);">Gérez vos dossiers en toute indépendance ({{ $clients->total() ?? 0 }} client(s))</p>
    </div>
    <a href="{{ route('gel-accountant.independant.clients.create') }}" class="gel-btn gel-btn-primary" style="font-weight:600;display:flex;align-items:center;gap:6px;border-radius:6px;padding:8px 14px;">
        <i class="bi bi-plus-circle"></i> Nouveau client
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success animate-fade delay-1">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
    </div>
@endif

<div class="pro-panel animate-fade delay-1">
    <div class="panel-body" style="padding-bottom: 0;">
        <form method="GET" action="{{ route('gel-accountant.independant.clients.index') }}" class="d-flex" style="gap:10px; margin-bottom: 20px;">
            <div class="input-group" style="max-width: 300px;">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Rechercher un client..." value="{{ request('search') }}">
            </div>
            <button type="submit" class="gel-btn gel-btn-outline">Rechercher</button>
        </form>
    </div>

    <table class="table mb-0">
        <thead class="bg-light">
            <tr>
                <th class="ps-4">Entreprise</th>
                <th>Contact</th>
                <th>Email</th>
                <th>Secteur</th>
                <th>Statut</th>
                <th class="text-end pe-4">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($clients as $client)
                <tr>
                    <td class="ps-4">
                        <div class="fw-bold">{{ $client->nom_entreprise }}</div>
                        <small class="text-muted">IFU: {{ $client->ifu ?? 'N/A' }}</small>
                    </td>
                    <td>{{ $client->contact_nom ?? '-' }}<br><small class="text-muted">{{ $client->telephone }}</small></td>
                    <td>{{ $client->email ?? '-' }}</td>
                    <td>{{ $client->secteur ?? '-' }}</td>
                    <td>
                        @if($client->type === 'manuel')
                            <span class="badge bg-secondary">Manuel</span>
                        @else
                            <span class="badge bg-primary">Lié (Plateforme)</span>
                        @endif
                    </td>
                    <td class="text-end pe-4">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li><a class="dropdown-item" href="{{ route('gel-accountant.independant.clients.show', $client->id) }}"><i class="bi bi-eye me-2 text-muted"></i> Gérer le dossier</a></li>
                                <li><a class="dropdown-item" href="{{ route('gel-accountant.independant.clients.edit', $client->id) }}"><i class="bi bi-pencil me-2 text-muted"></i> Modifier</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('gel-accountant.independant.clients.destroy', $client->id) }}" method="POST" onsubmit="return confirm('Attention : cette action est irréversible. Toutes les données seront supprimées. Confirmer ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i> Supprimer</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="bi bi-folder2-open display-4 d-block mb-3 opacity-50"></i>
                        Aucun client enregistré pour le moment.<br>
                        Cliquez sur "Nouveau client" pour commencer.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    @if($clients->hasPages())
        <div class="panel-body bg-light border-top d-flex justify-content-between align-items-center">
            {{ $clients->links() }}
        </div>
    @endif
</div>
@endsection
