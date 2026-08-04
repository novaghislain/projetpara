@extends('layouts.gel-accountant')
@section('title', 'Paramètres Espace Client - GEL Cabinet')

@section('content')
<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">Espace Client</h1>
        <p class="gel-page-subtitle">Gérez l'accès à votre portail externe pour vos clients</p>
    </div>
</div>

<div class="gel-card p-4 mb-4">
    <div class="gel-card-header p-4 mb-4" style="display:flex; justify-content: space-between; align-items: center;">
        <span style="font-weight:700;">Configuration du Portail</span>
        <div>
            @if($client->portal_active)
                <span class="gel-badge gel-badge-success">Actif</span>
            @else
                <span class="gel-badge gel-badge-danger">Désactivé</span>
            @endif
        </div>
    </div>
    
    <div class="gel-card-body p-4 mb-4">
        @if(session('success'))
            <div class="alert alert-success mb-3">{{ session('success') }}</div>
        @endif

        <div style="margin-bottom: 24px;">
            <label style="font-weight: 600; display: block; margin-bottom: 8px;">Lien d'invitation permanent</label>
            <p style="font-size: 13px; color: var(--gel-text-muted); margin-bottom: 12px;">Partagez ce lien avec vos clients finaux pour qu'ils puissent s'inscrire ou se connecter à leur espace dédié de manière autonome.</p>
            
            <div style="display: flex; gap: 12px; max-width: 600px;">
                <input type="text" readonly class="gel-form-control" value="{{ $portalUrl }}" id="portalLinkInput">
                <button type="button" class="gel-btn gel-btn-secondary" onclick="copyPortalLink()">
                    <i class="fas fa-copy"></i> Copier
                </button>
            </div>
        </div>

        <div style="display: flex; gap: 12px; margin-top: 32px;">
            <form action="{{ route('gel-accountant.settings.client-portal.toggle') }}" method="POST">
                @csrf
                <button type="submit" class="gel-btn {{ $client->portal_active ? 'gel-btn-danger' : 'gel-btn-success' }}">
                    <i class="fas fa-power-off"></i> {{ $client->portal_active ? 'Désactiver le portail' : 'Activer le portail' }}
                </button>
            </form>

            <form action="{{ route('gel-accountant.settings.client-portal.regenerate') }}" method="POST" onsubmit="return confirm('Êtes-vous sûr ? L\'ancien lien ne fonctionnera plus.');">
                @csrf
                <button type="submit" class="gel-btn gel-btn-secondary">
                    <i class="fas fa-sync-alt"></i> Régénérer le lien
                </button>
            </form>
        </div>
    </div>
</div>

<div class="gel-card p-4 mb-4">
    <div class="gel-card-header p-4 mb-4">
        <span style="font-weight:700;">Statistiques de l'Espace Client</span>
    </div>
    <div class="gel-card-body p-4 mb-4">
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
            <div style="padding: 16px; border: 1px solid var(--gel-border); border-radius: 8px; text-align: center;">
                <div style="font-size: 24px; font-weight: 700; color: var(--gel-primary);">0</div>
                <div style="font-size: 13px; color: var(--gel-text-muted);">Contacts inscrits</div>
            </div>
            <div style="padding: 16px; border: 1px solid var(--gel-border); border-radius: 8px; text-align: center;">
                <div style="font-size: 24px; font-weight: 700; color: var(--gel-primary);">0</div>
                <div style="font-size: 13px; color: var(--gel-text-muted);">Corrections proposées</div>
            </div>
            <div style="padding: 16px; border: 1px solid var(--gel-border); border-radius: 8px; text-align: center;">
                <div style="font-size: 24px; font-weight: 700; color: var(--gel-primary);">0</div>
                <div style="font-size: 13px; color: var(--gel-text-muted);">Tickets ouverts</div>
            </div>
        </div>
        <p style="font-size: 12px; color: var(--gel-text-muted); text-align: center; margin-top: 16px;">(Les statistiques seront mises à jour prochainement)</p>
    </div>
</div>

<div class="gel-card p-4 mb-4">
    <div class="gel-card-header p-4 mb-4">
        <span style="font-weight:700;">Contacts rattachés à ce cabinet</span>
    </div>
    <div class="gel-card-body p-4 mb-4">
        @if($portalContacts->isEmpty())
            <div class="gel-empty">
                <i class="bi bi-people"></i>
                <p>Aucun contact n'est encore inscrit sur le portail de ce client.</p>
            </div>
        @else
            <table class="gel-table">
                <thead>
                    <tr>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($portalContacts as $contact)
                        <tr>
                            <td>
                                <strong>{{ $contact->first_name }} {{ $contact->last_name }}</strong>
                                
                                @php
                                    $pendingNameCorrection = \App\Models\PortalContactCorrection::where('portal_contact_id', $contact->id)
                                        ->where('client_id', $client->id)
                                        ->whereIn('field_name', ['first_name', 'last_name'])
                                        ->where('status', 'pending')
                                        ->first();
                                @endphp
                                @if($pendingNameCorrection)
                                    <br><small style="color: var(--gel-warning);"><i class="fas fa-clock"></i> Modification proposée en attente</small>
                                @endif
                            </td>
                            <td>{{ $contact->email }}</td>
                            <td>
                                {{ $contact->phone ?? '—' }}
                                @php
                                    $pendingPhoneCorrection = \App\Models\PortalContactCorrection::where('portal_contact_id', $contact->id)
                                        ->where('client_id', $client->id)
                                        ->where('field_name', 'phone')
                                        ->where('status', 'pending')
                                        ->first();
                                @endphp
                                @if($pendingPhoneCorrection)
                                    <br><small style="color: var(--gel-warning);"><i class="fas fa-clock"></i> En attente : {{ $pendingPhoneCorrection->new_value }}</small>
                                @endif
                            </td>
                            <td>
                                @if($contact->pivot->is_active)
                                    <span class="gel-badge gel-badge-success">Actif</span>
                                @else
                                    <span class="gel-badge gel-badge-danger">Inactif</span>
                                @endif
                            </td>
                            <td>
                                <button class="gel-btn gel-btn-sm gel-btn-secondary" onclick="openCorrectionModal({{ $contact->id }}, '{{ addslashes($contact->first_name) }}', '{{ addslashes($contact->last_name) }}', '{{ addslashes($contact->phone) }}')">
                                    <i class="fas fa-edit"></i> Proposer une correction
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

{{-- Modal pour la proposition de correction --}}
<div id="correctionModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:white; border-radius:12px; width:100%; max-width:500px; padding:24px;">
        <h3 style="font-size:18px; font-weight:700; margin-bottom:16px;">Proposer une correction</h3>
        <p style="font-size:14px; color:var(--gel-text-secondary); margin-bottom:24px;">
            En raison de l'auto-souveraineté des données, vous ne pouvez pas modifier directement ces informations. Votre proposition sera soumise au client pour validation.
        </p>
        
        <form id="correctionForm" method="POST" action="">
            @csrf
            
            <div class="gel-form-group">
                <label>Champ à corriger</label>
                <select name="field_name" class="gel-form-select" id="correctionFieldSelect" onchange="updateCorrectionPlaceholder()" required>
                    <option value="first_name">Prénom</option>
                    <option value="last_name">Nom</option>
                    <option value="phone">Téléphone</option>
                </select>
            </div>
            
            <div class="gel-form-group">
                <label>Nouvelle valeur</label>
                <input type="text" name="new_value" id="correctionNewValue" class="gel-form-control" required>
            </div>
            
            <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:24px;">
                <button type="button" class="gel-btn gel-btn-secondary" onclick="document.getElementById('correctionModal').style.display='none'">Annuler</button>
                <button type="submit" class="gel-btn gel-btn-primary">Envoyer la proposition</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function copyPortalLink() {
    var copyText = document.getElementById("portalLinkInput");
    copyText.select();
    copyText.setSelectionRange(0, 99999); // Pour mobile
    navigator.clipboard.writeText(copyText.value).then(function() {
        showToast('Lien copié dans le presse-papiers !', 'success');
    });
}

const contactData = {};

function openCorrectionModal(id, firstName, lastName, phone) {
    contactData[id] = { first_name: firstName, last_name: lastName, phone: phone };
    
    const form = document.getElementById('correctionForm');
    // Mettre à jour l'action du formulaire (avec méthode POST)
    form.action = `/gel-accountant/clients/contacts/${id}/propose-correction`;
    
    // Initialiser le champ
    document.getElementById('correctionFieldSelect').value = 'first_name';
    document.getElementById('correctionNewValue').value = firstName;
    
    // Stocker l'ID actif
    document.getElementById('correctionModal').dataset.activeId = id;
    
    document.getElementById('correctionModal').style.display = 'flex';
}

function updateCorrectionPlaceholder() {
    const id = document.getElementById('correctionModal').dataset.activeId;
    const field = document.getElementById('correctionFieldSelect').value;
    const input = document.getElementById('correctionNewValue');
    
    if (contactData[id]) {
        input.value = contactData[id][field] || '';
    }
}
</script>
@endpush
@endsection
