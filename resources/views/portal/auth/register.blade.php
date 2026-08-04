@extends('layouts.portal')
@section('title', 'Inscription - Espace Client')
@section('auth-layout', true)

@section('content')
<div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px;">
    <div class="portal-card" style="width: 100%; max-width: 480px;">
        <div class="portal-text-center portal-mb-6">
            @if(isset($client) && $client->logo)
                <img src="{{ asset('storage/' . $client->logo) }}" alt="{{ $client->company_name }}" style="max-height: 48px; margin-bottom: 16px;">
            @endif
            <h1 class="portal-title">Créer un compte</h1>
            <p class="portal-subtitle">Rejoignez l'espace client de {{ $client->company_name ?? '' }}</p>
        </div>

        @if($errors->any())
            <div class="portal-alert portal-alert-danger">
                <ul style="padding-left: 20px; margin: 0;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('portal.register.submit', ['slug' => $slug]) }}">
            @csrf
            
            <div style="display: flex; gap: 16px;">
                <div class="portal-form-group" style="flex: 1;">
                    <label for="first_name" class="portal-label">Prénom *</label>
                    <input type="text" id="first_name" name="first_name" class="portal-input" value="{{ old('first_name') }}" required autofocus>
                </div>
                <div class="portal-form-group" style="flex: 1;">
                    <label for="last_name" class="portal-label">Nom *</label>
                    <input type="text" id="last_name" name="last_name" class="portal-input" value="{{ old('last_name') }}" required>
                </div>
            </div>
            
            <div class="portal-form-group">
                <label for="email" class="portal-label">Adresse e-mail professionnelle *</label>
                <input type="email" id="email" name="email" class="portal-input" value="{{ old('email') }}" required>
            </div>
            
            <div class="portal-form-group">
                <label for="password" class="portal-label">Mot de passe *</label>
                <input type="password" id="password" name="password" class="portal-input" required>
            </div>
            
            <div class="portal-form-group">
                <label for="password_confirmation" class="portal-label">Confirmer le mot de passe *</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="portal-input" required>
            </div>
            
            <button type="submit" class="portal-btn portal-btn-primary" style="width: 100%; margin-top: 8px;">
                Créer mon compte
            </button>
        </form>
        
        <div class="portal-text-center portal-mt-4" style="font-size: 14px;">
            Vous avez déjà un compte ? 
            <a href="{{ route('portal.login', ['slug' => $slug]) }}" style="font-weight: 600; color: var(--portal-accent); text-decoration: none;">Se connecter</a>
        </div>
    </div>
</div>
@endsection
