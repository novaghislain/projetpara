@extends('layouts.gel-business')

@section('title', 'Mon entreprise - GEL')

@section('content')
<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">Mon entreprise</h1>
        <p class="gel-page-subtitle">Informations de votre entreprise</p>
    </div>
</div>

{{-- L'entreprise est passée par le contrôleur (App\Models\Gel\Client) --}}

<div class="gel-grid-2">
    {{-- ─── Informations générales ─── --}}
    <div class="gel-card">
        <div class="gel-card-header">
            <strong>Informations générales</strong>
            @php
                $isAccountantUser = (auth()->user() && method_exists(auth()->user(), 'isAccountant') && auth()->user()->isAccountant()) || session('current_client_id');
            @endphp
            @if($entreprise && !$isAccountantUser)
            <button class="gel-btn gel-btn-sm gel-btn-secondary" onclick="openEditEntreprisePanel()">
                <i class="bi bi-pencil"></i> Modifier
            </button>
            @endif
        </div>
        
        <template id="editEntrepriseTemplate">
            <form method="POST" action="{{ route('gel-business.profile.update') }}" id="editEntrepriseForm">
                @csrf
                @method('PUT')
                <div class="gel-form-group">
                    <label>Nom de l'entreprise</label>
                    <input type="text" name="nom_entreprise" class="gel-form-control" value="{{ $entreprise->nom_entreprise ?? '' }}">
                </div>
                <div class="gel-form-group">
                    <label>Sigle</label>
                    <input type="text" name="sigle" class="gel-form-control" value="{{ $entreprise->sigle ?? '' }}">
                </div>
                <div class="gel-form-group">
                    <label>IFU</label>
                    <input type="text" name="ifu" class="gel-form-control" value="{{ $entreprise->ifu ?? '' }}">
                </div>
                <div class="gel-form-group">
                    <label>RC</label>
                    <input type="text" name="rc" class="gel-form-control" value="{{ $entreprise->rc ?? '' }}">
                </div>
                <div class="gel-form-group">
                    <label>Secteur</label>
                    <input type="text" name="secteur" class="gel-form-control" value="{{ $entreprise->secteur ?? '' }}">
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
        </template>
        
        <template id="editEntrepriseFooter">
            <button class="gel-btn gel-btn-secondary" onclick="closePanel()">Annuler</button>
            <button class="gel-btn gel-btn-primary" onclick="document.getElementById('editEntrepriseForm').submit()">Enregistrer</button>
        </template>
        
        <script>
            function openEditEntreprisePanel() {
                var bodyHtml = document.getElementById('editEntrepriseTemplate').innerHTML;
                var footerHtml = document.getElementById('editEntrepriseFooter').innerHTML;
                openPanel('Modifier l\'entreprise', bodyHtml, footerHtml);
            }
        </script>
        
        <div class="gel-card-body">
            @if($entreprise)
                <div>
                    <div class="gel-detail-row">
                        <span class="gel-detail-label">Raison sociale</span>
                        <span class="gel-detail-value">{{ $entreprise->nom_entreprise }}</span>
                    </div>
                    <div class="gel-detail-row">
                        <span class="gel-detail-label">Sigle</span>
                        <span class="gel-detail-value">{{ $entreprise->sigle ?? '—' }}</span>
                    </div>
                    <div class="gel-detail-row">
                        <span class="gel-detail-label">IFU</span>
                        <span class="gel-detail-value monospace">{{ $entreprise->ifu ?? '—' }}</span>
                    </div>
                    <div class="gel-detail-row">
                        <span class="gel-detail-label">RC</span>
                        <span class="gel-detail-value">{{ $entreprise->rc ?? '—' }}</span>
                    </div>
                    <div class="gel-detail-row">
                        <span class="gel-detail-label">Secteur</span>
                        <span class="gel-detail-value">{{ $entreprise->secteur ?? '—' }}</span>
                    </div>
                    <div class="gel-detail-row">
                        <span class="gel-detail-label">Email</span>
                        <span class="gel-detail-value">{{ $entreprise->email ?? '—' }}</span>
                    </div>
                    <div class="gel-detail-row">
                        <span class="gel-detail-label">Téléphone</span>
                        <span class="gel-detail-value">{{ $entreprise->telephone ?? '—' }}</span>
                    </div>
                    <div class="gel-detail-row">
                        <span class="gel-detail-label">Adresse</span>
                        <span class="gel-detail-value" style="text-align:right;">{{ $entreprise->adresse ?? '—' }}{{ $entreprise->ville ? ', ' . $entreprise->ville : '' }}</span>
                    </div>
                </div>
            @else
                <div class="gel-empty" style="padding:20px;">
                    <i class="bi bi-building" style="font-size:40px;color:var(--gel-text-muted);"></i>
                    <h3 style="font-size:16px;">Aucune information</h3>
                    <p>Les détails de votre entreprise ne sont pas encore renseignés.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- ─── Contact comptable ─── --}}
    <div class="gel-card">
        <div class="gel-card-header">
            <strong>Contact comptable</strong>
        </div>
        <div class="gel-card-body">
            @php $cabinet = $entreprise?->cabinet ?? null; @endphp
            @if($cabinet)
                <div>
                    <div class="gel-detail-row">
                        <span class="gel-detail-label">Cabinet</span>
                        <span class="gel-detail-value">{{ $cabinet->nom }}</span>
                    </div>
                    <div class="gel-detail-row">
                        <span class="gel-detail-label">Email</span>
                        <span class="gel-detail-value">{{ $cabinet->email ?? '—' }}</span>
                    </div>
                    <div class="gel-detail-row">
                        <span class="gel-detail-label">Téléphone</span>
                        <span class="gel-detail-value">{{ $cabinet->telephone ?? '—' }}</span>
                    </div>
                    <div class="gel-detail-row">
                        <span class="gel-detail-label">Adresse</span>
                        <span class="gel-detail-value" style="text-align:right;">{{ $cabinet->adresse ?? '—' }}{{ $cabinet->ville ? ', ' . $cabinet->ville : '' }}</span>
                    </div>
                </div>
            @else
                <div class="gel-contact-comptable">
                    <div class="gel-avatar gel-avatar-lg" style="background:#9CA3AF;font-size:24px;">
                        <i class="fas fa-building"></i>
                    </div>
                    <h3>Aucun comptable</h3>
                    <p>Vous n'avez pas encore de cabinet comptable attitré.</p>
                    @if(!auth()->user()->isAccountant())
                    <a href="{{ route('gel-business.inviter-comptable') }}" class="gel-btn gel-btn-primary">
                        <i class="bi bi-person-plus"></i> Inviter un collaborateur
                    </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

{{-- ─── Support ─── --}}
<div class="gel-card mb-4">
    <div class="gel-card-header">
        <strong>Support</strong>
    </div>
    <div class="gel-card-body">
        <p style="font-size:13px;color:var(--gel-text-secondary);margin-bottom:16px;">
            Besoin d'aide ? Contactez le support GEL ou votre cabinet comptable.
        </p>
        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <a href="mailto:support@gelcabinet.com" class="gel-btn gel-btn-secondary">
                <i class="bi bi-envelope"></i> support@gelcabinet.com
            </a>
            <a href="tel:+22901020304" class="gel-btn gel-btn-secondary">
                <i class="bi bi-telephone"></i> +229 01 02 03 04
            </a>
        </div>
    </div>
</div>
@endsection
