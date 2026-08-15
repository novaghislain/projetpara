@extends('layouts.gel-accountant')

@section('title', 'Clients - GEL Cabinet')

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
</style>

<div class="gel-page-header animate-fade" style="border-bottom: 1px solid var(--gel-border); padding-bottom: 12px; margin-bottom: 24px; display:flex; justify-content:space-between; align-items:flex-end;">
    <div>
        <h1 class="gel-page-title" style="font-size: 18px; font-weight: 700; color: var(--gel-text-primary); margin-bottom: 4px;">Dossiers Clients</h1>
        <p class="gel-page-subtitle" style="font-size: 12px; color: var(--gel-text-secondary);">{{ $clients->total() ?? 0 }} entreprise(s) cliente(s)</p>
    </div>
    <button class="gel-btn gel-btn-primary" onclick="openClientPanel()" style="font-weight:600;display:flex;align-items:center;gap:6px;border-radius:6px;padding:8px 14px;">
        <i class="bi bi-plus-circle"></i> Nouveau dossier
    </button>
</div>

<template id="newClientFormTemplate">
    <form method="POST" action="{{ route('gel-accountant.clients.store') }}" id="newClientForm">
        @csrf
        <div class="gel-form-group">
            <label>Nom de l'entreprise *</label>
            <input type="text" name="nom_entreprise" class="gel-form-control" required placeholder="Ex: SARL Mon Entreprise">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="gel-form-group">
                <label>Sigle</label>
                <input type="text" name="sigle" class="gel-form-control" placeholder="Ex: ME">
            </div>
            <div class="gel-form-group">
                <label>Secteur d'activité</label>
                <select name="secteur" class="gel-form-select">
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
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="gel-form-group">
                <label>IFU <span style="color:var(--gel-danger);font-weight:700;">*</span></label>
                <input type="text" name="ifu" class="gel-form-control" required
                    placeholder="Ex: 3201800001234"
                    pattern="[0-9]{13}"
                    title="L'IFU doit contenir exactement 13 chiffres"
                    maxlength="13"
                    oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,13); validateIFU(this)"
                    id="ifuInput">
                <small id="ifuHint" style="color:var(--gel-text-muted);font-size:11px;">13 chiffres obligatoires</small>
                <small id="ifuError" style="color:var(--gel-danger);font-size:11px;display:none;">L'IFU doit contenir exactement 13 chiffres.</small>
            </div>
            <div class="gel-form-group">
                <label>RCCM (Registre de Commerce)</label>
                <input type="text" name="rc" class="gel-form-control" placeholder="Ex: RB/COT/25 B 12345">
            </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="gel-form-group">
                <label>Email</label>
                <input type="email" name="email" class="gel-form-control" placeholder="contact@entreprise.com">
            </div>
            <div class="gel-form-group">
                <label>Téléphone</label>
                <input type="text" name="telephone" class="gel-form-control" placeholder="Ex: +229 97 00 00 00">
            </div>
        </div>
        <div style="display:grid;grid-template-columns:2fr 1fr;gap:12px;">
            <div class="gel-form-group">
                <label>Adresse</label>
                <input type="text" name="adresse" class="gel-form-control" placeholder="Quartier, rue...">
            </div>
            <div class="gel-form-group">
                <label>Ville</label>
                <input type="text" name="ville" class="gel-form-control" placeholder="Ex: Cotonou">
            </div>
        </div>
    </form>
    <script>
        function validateIFU(input) {
            var err = document.getElementById('ifuError');
            var hint = document.getElementById('ifuHint');
            if (input.value.length > 0 && input.value.length < 13) {
                err.style.display = 'block';
                hint.style.display = 'none';
                input.style.borderColor = 'var(--gel-danger)';
            } else if (input.value.length === 13) {
                err.style.display = 'none';
                hint.style.display = 'none';
                input.style.borderColor = 'var(--gel-success)';
            } else {
                err.style.display = 'none';
                hint.style.display = 'block';
                input.style.borderColor = '';
            }
        }
    </script>
</template>

<script>
    function openClientPanel() {
        const formHtml = document.getElementById('newClientFormTemplate').innerHTML;
        const buttonsHtml = '<div style="margin-top:20px;display:flex;justify-content:flex-end;gap:10px;"><button class="gel-btn gel-btn-secondary" onclick="closeSlidePanel()">Annuler</button><button class="gel-btn gel-btn-primary" onclick="document.getElementById(\'newClientForm\').submit()">Créer le client</button></div>';
        
        if (typeof openSlidePanel === 'function') {
            openSlidePanel('Nouveau client');
            document.getElementById('panelBody').innerHTML = formHtml + buttonsHtml;
        } else {
            console.error('openSlidePanel is not defined.');
        }
    }

    function editClient(id, nom, sigle, secteur, ifu, rc, email, tel, adresse, ville) {
        let formHtml = document.getElementById('newClientFormTemplate').innerHTML;
        
        // Change form ID, action, and add method spoofing
        formHtml = formHtml.replace('id="newClientForm"', 'id="editClientForm"');
        formHtml = formHtml.replace(`action="{{ route('gel-accountant.clients.store') }}"`, 'action="/gel-accountant/clients/' + id + '"');
        formHtml = formHtml.replace('@csrf', '@csrf <input type="hidden" name="_method" value="PUT">');

        const buttonsHtml = '<div style="margin-top:20px;display:flex;justify-content:flex-end;gap:10px;"><button class="gel-btn gel-btn-secondary" onclick="closeSlidePanel()">Annuler</button><button class="gel-btn gel-btn-primary" onclick="document.getElementById(\'editClientForm\').submit()">Mettre à jour</button></div>';
        
        if (typeof openSlidePanel === 'function') {
            openSlidePanel('Modifier le client');
            document.getElementById('panelBody').innerHTML = formHtml + buttonsHtml;
            
            // Populate data
            const form = document.getElementById('editClientForm');
            if(form.elements['nom_entreprise']) form.elements['nom_entreprise'].value = nom || '';
            if(form.elements['sigle']) form.elements['sigle'].value = sigle || '';
            if(form.elements['secteur']) form.elements['secteur'].value = secteur || '';
            if(form.elements['ifu']) form.elements['ifu'].value = ifu || '';
            if(form.elements['rc']) form.elements['rc'].value = rc || '';
            if(form.elements['email']) form.elements['email'].value = email || '';
            if(form.elements['telephone']) form.elements['telephone'].value = tel || '';
            if(form.elements['adresse']) form.elements['adresse'].value = adresse || '';
            if(form.elements['ville']) form.elements['ville'].value = ville || '';
            
            if(form.elements['ifu']) validateIFU(form.elements['ifu']);
        }
    }
</script>

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
    <div style="position:relative; width:240px; margin-left:auto;">
        <i class="bi bi-search" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--gel-text-muted);"></i>
        <input type="text" class="gel-form-control" style="padding-left:36px; width:100%;" placeholder="Rechercher un client..." id="searchClient" value="{{ request('q') }}">
    </div>
</div>

{{-- Tableau --}}
<div class="pro-panel animate-fade delay-1 mb-4">
    <div class="gel-card-body p-4 mb-4">
        @if($clients->count() > 0)
            <table class="gel-table">
                <thead>
                    <tr>
                        <th>Entreprise</th>
                        <th>Contact</th>
                        <th>IFU</th>
                        <th>RCCM</th>
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
                            <td>
                                @if($client->nif)
                                    <code style="font-size:12px;background:var(--gel-bg-light);padding:2px 6px;border-radius:4px;">{{ $client->nif }}</code>
                                @else
                                    <span style="color:var(--gel-danger);font-size:12px;"><i class="bi bi-exclamation-triangle"></i> Non renseigné</span>
                                @endif
                            </td>
                            <td>
                                @if($client->rc)
                                    <span style="font-size:12px;">{{ $client->rc }}</span>
                                @else
                                    <span style="color:var(--gel-text-muted);font-size:12px;">—</span>
                                @endif
                            </td>
                            <td>{{ $client->secteur ?? '—' }}</td>
                            <td>
                                @if($client->statut === 'actif')
                                    <span class="gel-badge gel-badge-success">Actif</span>
                                @else
                                    <span class="gel-badge gel-badge-warning">Inactif</span>
                                @endif
                            </td>
                            <td>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <a href="{{ route('gel-accountant.client.select', ['clientId' => $client->id]) }}" class="gel-btn gel-btn-sm gel-btn-primary" title="Travailler sur ce client">
                                        <i class="fas fa-desktop"></i> Sélectionner
                                    </a>
                                    <div class="gel-dropdown">
                                        <button class="gel-btn gel-btn-sm gel-btn-secondary" onclick="event.stopPropagation(); toggleDropdown('dropdownClient{{ $client->id }}')">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div class="gel-dropdown-menu" id="dropdownClient{{ $client->id }}" style="right:0;left:auto;">
                                            <button type="button" class="gel-dropdown-item" onclick="event.stopPropagation(); editClient({{ $client->id }}, '{{ addslashes($client->nom_entreprise) }}', '{{ addslashes($client->sigle) }}', '{{ addslashes($client->secteur) }}', '{{ addslashes($client->nif) }}', '{{ addslashes($client->rc) }}', '{{ addslashes($client->email) }}', '{{ addslashes($client->telephone) }}', '{{ addslashes($client->adresse) }}', '{{ addslashes($client->ville) }}')">
                                                <i class="fas fa-edit"></i> Modifier le client
                                            </button>
                                            <a href="{{ route('gel-accountant.comptabilite.plan-comptable', ['client_id' => $client->id]) }}" class="gel-dropdown-item">
                                                <i class="fas fa-list"></i> Comptabilité
                                            </a>
                                            <a href="{{ route('gel-accountant.comptabilite.grand-livre', ['client_id' => $client->id]) }}" class="gel-dropdown-item">
                                                <i class="fas fa-book"></i> Grand Livre
                                            </a>
                                            <a href="{{ route('gel-accountant.comptabilite.balance', ['client_id' => $client->id]) }}" class="gel-dropdown-item">
                                                <i class="fas fa-table"></i> Balance
                                            </a>
                                            <div class="gel-dropdown-divider"></div>
                                            <button type="button" class="gel-dropdown-item" style="color:var(--gel-danger);" onclick="if(confirm('{{ $client->statut === 'actif' ? 'Désactiver' : 'Activer' }} ce client ?')) window.location.href='{{ route('gel-accountant.clients.toggle', $client->id) }}'">
                                                <i class="fas fa-toggle-on"></i> {{ $client->statut === 'actif' ? 'Désactiver' : 'Activer' }}
                                            </button>
                                        </div>
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
