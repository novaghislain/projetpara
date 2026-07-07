@extends('layouts.gel-business')

@section('title', 'Mon entreprise - GEL')

@section('content')
<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">Mon entreprise</h1>
        <p class="gel-page-subtitle">Informations de votre entreprise</p>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
    {{-- Informations générales --}}
    <div class="gel-card">
        <div class="gel-card-header">
            <strong>Informations générales</strong>
            <button class="gel-btn gel-btn-sm gel-btn-secondary" onclick="openPanel('Modifier l\\'entreprise', `
                <form method="POST" action="{{ route('gel-business.profile.update') }}" id="editEntrepriseForm">
                    @csrf @method('PUT')
                    <div class="gel-form-group">
                        <label>Nom de l'entreprise</label>
                        <input type="text" name="nom_entreprise" class="gel-form-control" value="{{ $entreprise->nom_entreprise ?? '' }}">
                    </div>
                    <div class="gel-form-group">
                        <label>Sigle</label>
                        <input type="text" name="sigle" class="gel-form-control" value="{{ $entreprise->sigle ?? '' }}">
                    </div>
                    <div class="gel-form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="gel-form-control" value="{{ $entreprise->email ?? '' }}">
                    </div>
                    <div class="gel-form-group">
                        <label>Téléphone</label>
                        <input type="text" name="telephone" class="gel-form-control" value="{{ $entreprise->telephone ?? '' }}">
                    </div>
                    <div class="gel-form-group">
                        <label>Adresse</label>
                        <textarea name="adresse" class="gel-form-control" rows="2">{{ $entreprise->adresse ?? '' }}</textarea>
                    </div>
                    <div class="gel-form-group">
                        <label>Ville</label>
                        <input type="text" name="ville" class="gel-form-control" value="{{ $entreprise->ville ?? '' }}">
                    </div>
                </form>
            `, '<button class="gel-btn gel-btn-secondary" onclick="closePanel()">Annuler</button><button class="gel-btn gel-btn-primary" onclick="document.getElementById(\'editEntrepriseForm\').submit()">Enregistrer</button>')">
                <i class="bi bi-pencil"></i> Modifier
            </button>
        </div>
        <div class="gel-card-body">
            @php $entreprise = auth()->user()->client ?? auth()->user()->activeClient ?? null; @endphp
            @if($entreprise)
                <div style="display:grid;gap:8px;">
                    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--gel-border-light);">
                        <span style="color:var(--gel-text-secondary);">Raison sociale</span>
                        <span style="font-weight:600;">{{ $entreprise->nom_entreprise }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--gel-border-light);">
                        <span style="color:var(--gel-text-secondary);">Sigle</span>
                        <span style="font-weight:600;">{{ $entreprise->sigle ?? '—' }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--gel-border-light);">
                        <span style="color:var(--gel-text-secondary);">IFU</span>
                        <span style="font-weight:600;">{{ $entreprise->ifu ?? '—' }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--gel-border-light);">
                        <span style="color:var(--gel-text-secondary);">RC</span>
                        <span style="font-weight:600;">{{ $entreprise->rc ?? '—' }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--gel-border-light);">
                        <span style="color:var(--gel-text-secondary);">Secteur</span>
                        <span style="font-weight:600;">{{ $entreprise->secteur_activite ?? '—' }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:8px 0;">
                        <span style="color:var(--gel-text-secondary);">Ville</span>
                        <span style="font-weight:600;">{{ $entreprise->ville ?? '—' }}</span>
                    </div>
                </div>
            @else
                <div class="gel-empty" style="padding:20px;">
                    <i class="bi bi-building"></i>
                    <h3 style="font-size:16px;">Aucune information</h3>
                    <p>Les détails de votre entreprise ne sont pas encore renseignés.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Contact comptable --}}
    <div class="gel-card">
        <div class="gel-card-header">
            <strong>Contact comptable</strong>
        </div>
        <div class="gel-card-body">
            @php
                $cabinet = $entreprise->cabinet ?? null;
            @endphp
            @if($cabinet)
                <div style="display:grid;gap:8px;">
                    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--gel-border-light);">
                        <span style="color:var(--gel-text-secondary);">Cabinet</span>
                        <span style="font-weight:600;">{{ $cabinet->nom }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--gel-border-light);">
                        <span style="color:var(--gel-text-secondary);">Email</span>
                        <span style="font-weight:600;">{{ $cabinet->email ?? '—' }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--gel-border-light);">
                        <span style="color:var(--gel-text-secondary);">Téléphone</span>
                        <span style="font-weight:600;">{{ $cabinet->telephone ?? '—' }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:8px 0;">
                        <span style="color:var(--gel-text-secondary);">Adresse</span>
                        <span style="font-weight:600;text-align:right;">{{ $cabinet->adresse ?? '—' }}<br>{{ $cabinet->ville ?? '' }}</span>
                    </div>
                </div>
            @else
                <div class="gel-empty" style="padding:20px;">
                    <i class="bi bi-headset"></i>
                    <h3 style="font-size:16px;">Aucun comptable</h3>
                    <p>Vous n'avez pas encore de cabinet comptable attitré.</p>
                    <a href="{{ route('gel-business.inviter-comptable') }}" class="gel-btn gel-btn-primary">
                        <i class="bi bi-person-plus"></i> Inviter un comptable
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Contact & Support --}}
<div class="gel-card" style="margin-top:20px;">
    <div class="gel-card-header">
        <strong>Support</strong>
    </div>
    <div class="gel-card-body">
        <p style="font-size:13px;color:var(--gel-text-secondary);">
            Besoin d'aide ? Contactez le support GEL ou votre cabinet comptable.
        </p>
        <div style="display:flex;gap:12px;">
            <a href="mailto:support@gelcabinet.com" class="gel-btn gel-btn-secondary">
                <i class="bi bi-envelope"></i> support@gelcabinet.com
            </a>
            <a href="tel:+22901020304" class="gel-btn gel-btn-secondary">
                <i class="bi bi-telephone"></i> +229 01 02 03 04
            </a>
        </div>
    </div>
</div>

<script>
    function openPanel(title, bodyHtml, footerHtml) {
        document.getElementById('gelPanelTitle').textContent = title;
        document.getElementById('gelPanelBody').innerHTML = bodyHtml;
        document.getElementById('gelPanelFooter').innerHTML = footerHtml || '';
        document.getElementById('gelPanel').classList.add('open');
        document.getElementById('gelPanelOverlay').classList.add('open');
    }
    function closePanel() {
        document.getElementById('gelPanel').classList.remove('open');
        document.getElementById('gelPanelOverlay').classList.remove('open');
    }
</script>
@endsection
