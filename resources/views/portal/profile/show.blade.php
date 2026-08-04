@extends('layouts.portal')
@section('title', 'Mon Profil - Espace Client')

@section('content')
<div class="portal-page-header portal-mb-6">
    <h1 class="portal-title">Mon Profil</h1>
    <p class="portal-subtitle">Gérez vos informations personnelles et vérifiez les propositions de correction.</p>
</div>

@if(session('success'))
    <div class="portal-alert portal-alert-success">
        {{ session('success') }}
    </div>
@endif

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
    
    {{-- Formulaire de mise à jour --}}
    <div class="portal-card">
        <h2 style="font-size: 18px; font-weight: 700; margin-bottom: 24px;">Informations Personnelles</h2>
        
        <form method="POST" action="{{ route('portal.profile.update', ['slug' => $slug]) }}">
            @csrf
            
            <div style="display: flex; gap: 16px;">
                <div class="portal-form-group" style="flex: 1;">
                    <label for="first_name" class="portal-label">Prénom</label>
                    <input type="text" id="first_name" name="first_name" class="portal-input" value="{{ old('first_name', $user->first_name) }}" required>
                </div>
                <div class="portal-form-group" style="flex: 1;">
                    <label for="last_name" class="portal-label">Nom</label>
                    <input type="text" id="last_name" name="last_name" class="portal-input" value="{{ old('last_name', $user->last_name) }}" required>
                </div>
            </div>
            
            <div class="portal-form-group">
                <label for="email" class="portal-label">Adresse e-mail</label>
                <input type="email" id="email" class="portal-input" value="{{ $user->email }}" disabled style="background-color: var(--portal-bg); cursor: not-allowed;">
                <small style="color: var(--portal-text-muted); font-size: 12px;">L'adresse e-mail ne peut pas être modifiée ici.</small>
            </div>
            
            <div class="portal-form-group">
                <label for="phone" class="portal-label">Numéro de téléphone</label>
                <input type="text" id="phone" name="phone" class="portal-input" value="{{ old('phone', $user->phone) }}">
            </div>
            
            <div style="margin-top: 24px;">
                <button type="submit" class="portal-btn portal-btn-primary">Enregistrer les modifications</button>
            </div>
        </form>
    </div>

    {{-- Alertes de corrections --}}
    <div>
        <div class="portal-card" style="border-top: 4px solid var(--portal-accent);">
            <h2 style="font-size: 16px; font-weight: 700; margin-bottom: 16px;">Corrections proposées par {{ $client->company_name }}</h2>
            
            @if($pendingCorrections->isEmpty())
                <p style="font-size: 14px; color: var(--portal-text-secondary);">Aucune correction en attente.</p>
            @else
                @foreach($pendingCorrections as $correction)
                    <div style="border: 1px solid var(--portal-border); border-radius: 8px; padding: 16px; margin-bottom: 12px; font-size: 14px;">
                        <div style="font-weight: 600; margin-bottom: 8px;">Le champ <span style="color: var(--portal-accent);">{{ $correction->field_name }}</span> a été corrigé par le cabinet.</div>
                        <div style="margin-bottom: 12px;">
                            <del style="color: var(--portal-danger); margin-right: 8px;">{{ $correction->old_value ?? '(Vide)' }}</del> 
                            <i class="fas fa-arrow-right" style="color: var(--portal-text-muted); font-size: 12px; margin-right: 8px;"></i> 
                            <span style="color: var(--portal-success); font-weight: 600;">{{ $correction->new_value }}</span>
                        </div>
                        
                        <div style="display: flex; gap: 8px;">
                            <form method="POST" action="{{ route('portal.profile.correction', ['slug' => $slug, 'id' => $correction->id]) }}" style="flex: 1;">
                                @csrf
                                <input type="hidden" name="action" value="accept">
                                <button type="submit" class="portal-btn portal-btn-primary" style="width: 100%; padding: 6px; font-size: 13px;">Accepter</button>
                            </form>
                            <form method="POST" action="{{ route('portal.profile.correction', ['slug' => $slug, 'id' => $correction->id]) }}" style="flex: 1;">
                                @csrf
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" class="portal-btn portal-btn-secondary" style="width: 100%; padding: 6px; font-size: 13px; color: var(--portal-danger); border-color: #FCA5A5;">Refuser</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
        
        <div class="portal-card" style="margin-top: 24px;">
            <h2 style="font-size: 16px; font-weight: 700; margin-bottom: 16px; color: var(--portal-danger);">Zone de danger</h2>
            <p style="font-size: 13px; color: var(--portal-text-secondary); margin-bottom: 16px;">
                Vous pouvez retirer votre accès à ce cabinet à tout moment. Cette action est irréversible.
            </p>
            <button type="button" class="portal-btn portal-btn-secondary" style="color: var(--portal-danger); border-color: #FCA5A5; width: 100%;">Révoquer mon accès</button>
        </div>
    </div>
</div>
@endsection
