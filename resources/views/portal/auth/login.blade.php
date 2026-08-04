@extends('layouts.portal')
@section('title', 'Connexion - Espace Client')
@section('auth-layout', true)

@section('content')
<div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px;">
    <div class="portal-card" style="width: 100%; max-width: 400px;">
        <div class="portal-text-center portal-mb-6">
            @if(isset($client) && $client->logo)
                <img src="{{ asset('storage/' . $client->logo) }}" alt="{{ $client->company_name }}" style="max-height: 48px; margin-bottom: 16px;">
            @endif
            <h1 class="portal-title">Bienvenue</h1>
            <p class="portal-subtitle">Connectez-vous à votre espace client {{ $client->company_name ?? '' }}</p>
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

        <form method="POST" action="{{ route('portal.login.submit', ['slug' => $slug]) }}">
            @csrf
            
            <div class="portal-form-group">
                <label for="email" class="portal-label">Adresse e-mail</label>
                <input type="email" id="email" name="email" class="portal-input" value="{{ old('email') }}" required autofocus>
            </div>
            
            <div class="portal-form-group">
                <label for="password" class="portal-label">Mot de passe</label>
                <input type="password" id="password" name="password" class="portal-input" required>
            </div>
            
            <div class="portal-form-group" style="display: flex; align-items: center; justify-content: space-between;">
                <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; cursor: pointer;">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    Se souvenir de moi
                </label>
                <a href="#" style="font-size: 13px; color: var(--portal-text-secondary); text-decoration: none;">Mot de passe oublié ?</a>
            </div>
            
            <button type="submit" class="portal-btn portal-btn-primary" style="width: 100%;">
                Se connecter
            </button>
        </form>
        
        <div class="portal-text-center portal-mt-4" style="font-size: 14px;">
            Vous n'avez pas de compte ? 
            <a href="{{ route('portal.register', ['slug' => $slug]) }}" style="font-weight: 600; color: var(--portal-accent); text-decoration: none;">S'inscrire</a>
        </div>
    </div>
</div>
@endsection
