@extends('layouts.gel-secretary')

@section('title', 'Créer mon entreprise')

@push('styles')
<style>
    .setup-container {
        max-width: 600px;
        margin: 40px auto;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        padding: 30px;
    }

    .setup-header {
        text-align: center;
        margin-bottom: 30px;
    }

    .setup-header i {
        font-size: 40px;
        color: var(--sec-primary);
        margin-bottom: 15px;
    }

    .setup-header h2 {
        color: var(--sec-text-primary);
        font-size: 24px;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .setup-header p {
        color: var(--sec-text-muted);
        font-size: 14px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-weight: 500;
        color: var(--sec-text-primary);
        margin-bottom: 8px;
        font-size: 14px;
    }

    .form-control {
        width: 100%;
        padding: 10px 15px;
        border: 1px solid var(--sec-border);
        border-radius: 6px;
        font-size: 14px;
        transition: border-color 0.2s;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--sec-primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .btn-submit {
        width: 100%;
        padding: 12px;
        background: var(--sec-primary);
        color: #fff;
        border: none;
        border-radius: 6px;
        font-weight: 500;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .btn-submit:hover {
        background: var(--sec-primary-hover);
    }

    .alert {
        padding: 12px;
        border-radius: 6px;
        margin-bottom: 20px;
        font-size: 14px;
    }

    .alert-danger {
        background: #FEF2F2;
        color: #EF4444;
        border: 1px solid #FCA5A5;
    }
</style>
@endpush

@section('content')
<div class="setup-container">
    <div class="setup-header">
        <i class="fas fa-building"></i>
        <h2>Bienvenue sur votre Espace Secrétaire</h2>
        <p>Pour commencer à utiliser toutes les fonctionnalités (documents, tâches, agenda, etc.), veuillez configurer votre entreprise de secrétariat indépendant.</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('gel-secretary.autonomous.enterprise.store') }}">
        @csrf

        <div class="form-group">
            <label for="nom_entreprise">Nom de votre entreprise / cabinet <span class="text-danger">*</span></label>
            <input type="text" id="nom_entreprise" name="nom_entreprise" class="form-control" placeholder="Ex: Marie Secrétariat Pro" required value="{{ old('nom_entreprise', auth()->user()->name . ' Secrétariat') }}">
        </div>

        <div class="form-group">
            <label for="email">Adresse Email professionnelle <span class="text-danger">*</span></label>
            <input type="email" id="email" name="email" class="form-control" placeholder="contact@entreprise.com" required value="{{ old('email', auth()->user()->email) }}">
        </div>

        <div class="form-group">
            <label for="telephone">Téléphone professionnel</label>
            <input type="text" id="telephone" name="telephone" class="form-control" placeholder="Ex: +229 00 00 00 00" value="{{ old('telephone') }}">
        </div>

        <button type="submit" class="btn-submit">Créer mon entreprise et démarrer</button>
    </form>
</div>
@endsection
