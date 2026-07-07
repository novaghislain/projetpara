@extends('layouts.gel-accountant')

@section('title', 'Clients - GEL Cabinet')

@section('content')
<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">Clients</h1>
        <p class="gel-page-subtitle">{{ $clients->total() ?? 0 }} entreprise(s) cliente(s)</p>
    </div>
    <button class="gel-btn gel-btn-primary" onclick="openPanel('Nouveau client', `
        <form method="POST" action="{{ route('gel-accountant.clients.store') }}" id="newClientForm">
            @csrf
            <div class="gel-form-group">
                <label>Nom de l'entreprise *</label>
                <input type="text" name="nom_entreprise" class="gel-form-control" required>
            </div>
            <div class="gel-form-group">
                <label>Sigle</label>
                <input type="text" name="sigle" class="gel-form-control">
            </div>
            <div class="gel-form-group">
                <label>Email</label>
                <input type="email" name="email" class="gel-form-control">
            </div>
            <div class="gel-form-group">
                <label>Téléphone</label>
                <input type="text" name="telephone" class="gel-form-control">
            </div>
            <div class="gel-form-group">
                <label>IFU</label>
                <input type="text" name="ifu" class="gel-form-control">
            </div>
            <div class="gel-form-group">
                <label>Secteur d'activité</label>
                <select name="secteur_activite" class="gel-form-select">
                    <option value="">Sélectionner...</option>
                    <option value="commerce">Commerce</option>
                    <option value="industrie">Industrie</option>
                    <option value="service">Service</option>
                    <option value="hotellerie">Hôtellerie</option>
                    <option value="transport">Transport</option>
                    <option value="immobilier">Immobilier</option>
                    <option value="sante">Santé</option>
                    <option value="autre">Autre</option>
                </select>
            </div>
            <div class="gel-form-group">
                <label>Adresse</label>
                <textarea name="adresse" class="gel-form-control" rows="2"></textarea>
            </div>
        </form>
    `, '<button class="gel-btn gel-btn-secondary" onclick="closePanel()">Annuler</button><button class="gel-btn gel-btn-primary" onclick="document.getElementById(\'newClientForm\').submit()">Créer le client</button>')">
        <i class="bi bi-plus-circle"></i> Nouveau client
    </button>
</div>

{{-- Filtres --}}
<div class="gel-filters">
    <div class="gel-filter-group">
        <label>Statut</label>
        <select class="gel-filter-select" onchange="if(this.value) window.location.href='{{ route('gel-accountant.clients') }}?statut='+this.value">
            <option value="">Tous</option>
            <option value="actif" {{ request('statut') === 'actif' ? 'selected' : '' }}>Actifs</option>
            <option value="inactif" {{ request('statut') === 'inactif' ? 'selected' : '' }}>Inactifs</option>
        </select>
    </div>
    <div class="gel-search" style="width:240px;margin-left:auto;">
        <i class="bi bi-search"></i>
        <input type="text" placeholder="Rechercher un client..." id="searchClient" value="{{ request('q') }}">
    </div>
</div>

{{-- Tableau --}}
<div class="gel-card">
    <div class="gel-card-body" style="padding:0;">
        @if($clients->count() > 0)
            <table class="gel-table">
                <thead>
                    <tr>
                        <th>Entreprise</th>
                        <th>Contact</th>
                        <th>IFU</th>
                        <th>Secteur</th>
                        <th>Statut</th>
                        <th style="width:120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($clients as $client)
                        <tr>
                            <td>
                                <strong>{{ $client->nom_entreprise }}</strong>
                                @if($client->sigle)
                                    <br><small style="color:var(--gel-text-muted);">{{ $client->sigle }}</small>
                                @endif
                            </td>
                            <td>
                                @if($client->email)<div><i class="bi bi-envelope"></i> {{ $client->email }}</div>@endif
                                @if($client->telephone)<div><i class="bi bi-telephone"></i> {{ $client->telephone }}</div>@endif
                            </td>
                            <td>{{ $client->ifu ?? '—' }}</td>
                            <td>{{ $client->secteur_activite ?? '—' }}</td>
                            <td>
                                @if($client->statut === 'actif')
                                    <span class="gel-badge gel-badge-success">Actif</span>
                                @else
                                    <span class="gel-badge gel-badge-warning">Inactif</span>
                                @endif
                            </td>
                            <td>
                                <div class="gel-dropdown">
                                    <button class="gel-btn gel-btn-sm gel-btn-secondary" onclick="event.stopPropagation();this.nextElementSibling.classList.toggle('show')">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <div class="gel-dropdown-menu">
                                        <a href="{{ route('gel-accountant.comptabilite.plan-comptable', ['client_id' => $client->id]) }}" class="gel-dropdown-item">
                                            <i class="bi bi-list-columns"></i> Comptabilité
                                        </a>
                                        <a href="{{ route('gel-accountant.comptabilite.grand-livre', ['client_id' => $client->id]) }}" class="gel-dropdown-item">
                                            <i class="bi bi-book"></i> Grand Livre
                                        </a>
                                        <a href="{{ route('gel-accountant.comptabilite.balance', ['client_id' => $client->id]) }}" class="gel-dropdown-item">
                                            <i class="bi bi-table"></i> Balance
                                        </a>
                                        <div class="gel-dropdown-divider"></div>
                                        <button class="gel-dropdown-item" onclick="if(confirm('Activer ce client ?'))window.location.href='{{ route('gel-accountant.clients.toggle', $client->id) }}'">
                                            <i class="bi bi-toggle-on"></i>
                                            {{ $client->statut === 'actif' ? 'Désactiver' : 'Activer' }}
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="gel-empty">
                <i class="bi bi-building"></i>
                <h3>Aucun client</h3>
                <p>Vous n'avez pas encore de client. Créez votre premier client.</p>
            </div>
        @endif
    </div>
</div>

@if($clients->hasPages())
    <div class="gel-pagination">
        {{ $clients->links() }}
    </div>
@endif

<script>
    document.getElementById('searchClient')?.addEventListener('keyup', function(e) {
        if (e.key === 'Enter' && this.value.trim()) {
            window.location.href = '{{ route('gel-accountant.clients') }}?q=' + encodeURIComponent(this.value.trim());
        }
    });
</script>
@endsection
