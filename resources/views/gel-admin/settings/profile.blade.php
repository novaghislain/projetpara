@extends('layouts.gel-admin')

@section('title', 'Profil Entreprise')

@section('content')
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Profil & Identité</h1>
        <p class="admin-page-sub">Informations générales de votre cabinet ou entreprise.</p>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="admin-card">
            <div class="admin-card-header">
                <div class="admin-card-title">Informations de base</div>
            </div>
            <div class="admin-card-body">
                <form action="{{ route('gel-admin.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Raison sociale / Nom complet</label>
                            <input type="text" class="form-control" name="nom" value="{{ old('nom', $profile['nom'] ?? '') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Adresse Email officielle</label>
                            <input type="email" class="form-control" name="email" value="{{ old('email', $profile['email'] ?? '') }}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Téléphone</label>
                            <input type="text" class="form-control" name="telephone" value="{{ old('telephone', $profile['telephone'] ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Site Web</label>
                            <input type="url" class="form-control" name="site_web" value="{{ old('site_web', $profile['site_web'] ?? '') }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Adresse physique</label>
                        <input type="text" class="form-control" name="adresse" value="{{ old('adresse', $profile['adresse'] ?? '') }}">
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Ville</label>
                            <input type="text" class="form-control" name="ville" value="{{ old('ville', $profile['ville'] ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Pays</label>
                            <input type="text" class="form-control" name="pays" value="{{ old('pays', $profile['pays'] ?? '') }}">
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Numéro IFU</label>
                            <input type="text" class="form-control" name="ifu" value="{{ old('ifu', $profile['ifu'] ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Numéro RCCM</label>
                            <input type="text" class="form-control" name="rccm" value="{{ old('rccm', $profile['rccm'] ?? '') }}">
                        </div>
                    </div>

                    <div class="mb-4">
                        @if($type !== 'client')
                        <label class="form-label">Logo de l'entreprise</label>
                        <input type="file" class="form-control" name="logo" accept="image/*">
                        @if($profile['logo'])
                            <div class="mt-2">
                                <img src="{{ asset('storage/' . $profile['logo']) }}" alt="Logo" style="height: 60px; border-radius: 4px; border: 1px solid #ccc;">
                            </div>
                        @endif
                        @endif
                    </div>

                    <button type="submit" class="admin-btn admin-btn-primary">Enregistrer les modifications</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="admin-card">
            <div class="admin-card-header">
                <div class="admin-card-title text-danger"><i class="fas fa-exclamation-triangle me-2"></i> Zone Dangereuse</div>
            </div>
            <div class="admin-card-body">
                <p class="text-muted small">Transférer la propriété de cet espace administrateur à un autre membre de votre équipe.</p>
                <form action="{{ route('gel-admin.profile.transfer') }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir transférer la propriété ? Vous perdrez les droits administrateur.');">
                    @csrf
                    <div class="mb-3">
                        <select class="form-select" name="new_owner_id" required>
                            <option value="">Sélectionner un membre...</option>
                                @php
                                    $team = \App\Models\User::where('client_id', auth()->user()->client_id)
                                        ->orWhere('entreprise_id', auth()->user()->entreprise_id)
                                        ->orWhere('cabinet_id', auth()->user()->cabinet_id)
                                        ->where('id', '!=', auth()->id())->get();
                                @endphp
                            @foreach($team as $m)
                                <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="admin-btn btn btn-outline-danger w-100">Transférer la propriété</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
