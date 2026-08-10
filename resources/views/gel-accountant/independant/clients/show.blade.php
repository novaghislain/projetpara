@extends('layouts.gel-accountant')

@section('title', $client->nom_entreprise . ' - Dossier Client')

@section('content')
<style>
  .pro-panel {
    background: white; border-radius: 8px; border: 1px solid var(--gel-border);
    box-shadow: 0 1px 3px rgba(0,0,0,0.02); display: flex; flex-direction: column;
  }
  .panel-body { padding: 24px; }
</style>

<div class="gel-page-header" style="border-bottom: 1px solid var(--gel-border); padding-bottom: 12px; margin-bottom: 24px; display:flex; justify-content:space-between; align-items:flex-end;">
    <div>
        <a href="{{ route('gel-accountant.independant.clients.index') }}" class="text-decoration-none text-muted mb-2 d-inline-block"><i class="bi bi-arrow-left"></i> Retour aux clients</a>
        <h1 class="gel-page-title" style="font-size: 20px; font-weight: 700; color: var(--gel-text-primary); margin-bottom: 4px;">{{ $client->nom_entreprise }}</h1>
        <p class="gel-page-subtitle" style="font-size: 13px; color: var(--gel-text-secondary);">
            IFU: {{ $client->ifu ?? 'N/A' }} | RCCM: {{ $client->rccm ?? 'N/A' }}
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('gel-accountant.independant.clients.edit', $client->id) }}" class="gel-btn gel-btn-outline" style="font-weight:600;display:flex;align-items:center;gap:6px;border-radius:6px;padding:8px 14px;">
            <i class="bi bi-pencil"></i> Éditer
        </a>
        <a href="{{ route('gel-accountant.client.select', $client->id) }}" class="gel-btn gel-btn-primary" style="font-weight:600;display:flex;align-items:center;gap:6px;border-radius:6px;padding:8px 14px;">
            <i class="bi bi-box-arrow-in-right"></i> Ouvrir la comptabilité
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
    </div>
@endif

<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="pro-panel h-100">
            <div class="panel-body">
                <h5 class="fw-bold mb-4 border-bottom pb-2" style="font-size: 15px;">Informations Générales</h5>
                
                <div class="mb-3">
                    <small class="text-muted d-block text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Secteur</small>
                    <div class="fw-medium">{{ $client->secteur ?? 'Non renseigné' }}</div>
                </div>
                
                <div class="mb-3">
                    <small class="text-muted d-block text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Adresse</small>
                    <div class="fw-medium">{{ $client->adresse ?? 'Non renseignée' }}</div>
                </div>

                <h5 class="fw-bold mt-4 mb-3 border-bottom pb-2" style="font-size: 15px;">Contact</h5>
                
                <div class="mb-3">
                    <small class="text-muted d-block text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Nom</small>
                    <div class="fw-medium">{{ $client->contact_nom ?? 'Non renseigné' }}</div>
                </div>
                
                <div class="mb-3">
                    <small class="text-muted d-block text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Email</small>
                    <div class="fw-medium"><a href="mailto:{{ $client->email }}">{{ $client->email ?? 'Non renseigné' }}</a></div>
                </div>
                
                <div class="mb-3">
                    <small class="text-muted d-block text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Téléphone</small>
                    <div class="fw-medium">{{ $client->telephone ?? 'Non renseigné' }}</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8 mb-4">
        <div class="pro-panel h-100">
            <div class="panel-body">
                <h5 class="fw-bold mb-4 border-bottom pb-2" style="font-size: 15px;">Connexion Plateforme (Optionnel)</h5>
                
                @if($client->type === 'manuel' && !$client->invitation_token)
                    <div class="text-center py-4">
                        <i class="bi bi-link-45deg display-4 text-muted mb-3 opacity-50"></i>
                        <h6>Ce client est géré manuellement.</h6>
                        <p class="text-muted small mx-auto" style="max-width: 400px;">
                            Vous pouvez lui envoyer un lien d'invitation. S'il l'accepte et crée un compte, il aura accès à son portail client en lecture seule pour voir vos travaux comptables.
                        </p>
                        <button class="btn btn-outline-primary mt-2" onclick="generateLink({{ $client->id }})">
                            <i class="bi bi-envelope me-2"></i> Générer un lien d'invitation
                        </button>
                    </div>
                @elseif($client->invitation_token && !$client->is_linked)
                    <div class="alert alert-warning border-0">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-clock-history me-2 fs-5"></i>
                            <h6 class="mb-0 fw-bold">Invitation en attente</h6>
                        </div>
                        <p class="small mb-2">Envoyez ce lien à votre client pour qu'il rejoigne la plateforme :</p>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control" id="inviteLink" value="{{ route('gel-accountant.independant.client.accept-invitation', ['token' => $client->invitation_token]) }}" readonly>
                            <button class="btn btn-outline-secondary" type="button" onclick="copyLink()">Copier</button>
                        </div>
                    </div>
                @else
                    <div class="alert alert-success border-0 d-flex align-items-center">
                        <i class="bi bi-check-circle-fill fs-3 me-3"></i>
                        <div>
                            <h6 class="fw-bold mb-1">Client Connecté</h6>
                            <p class="small mb-0">Ce client a accès à son portail d'entreprise sur GEL CAB.</p>
                        </div>
                    </div>
                @endif
                
                @if($client->notes)
                    <h5 class="fw-bold mt-5 mb-3 border-bottom pb-2" style="font-size: 15px;">Notes du dossier</h5>
                    <div class="p-3 bg-light rounded text-muted" style="font-size: 14px; white-space: pre-wrap;">{{ $client->notes }}</div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    function copyLink() {
        var copyText = document.getElementById("inviteLink");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(copyText.value);
        alert("Lien copié dans le presse-papier !");
    }
    
    function generateLink(id) {
        if(!confirm("Générer un lien d'invitation unique pour ce client ?")) return;
        
        fetch(`/gel-accountant/independant/clients/${id}/invitation`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            window.location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert("Une erreur est survenue.");
        });
    }
</script>
@endsection
