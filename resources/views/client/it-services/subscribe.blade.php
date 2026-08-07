@extends('layouts.portal')

@section('title', 'Souscrire à une Mission IT')

@section('content')
<div class="container py-4">
    <div class="mb-4">
        <a href="{{ route('client.it.index', ['slug' => $slug]) }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Retour</a>
    </div>

    <div class="card shadow-sm max-w-lg mx-auto" style="max-width: 600px;">
        <div class="card-header bg-white">
            <h5 class="mb-0">Nouvelle Mission Informatique</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('client.it.store-mission', ['slug' => $slug]) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Type de Service <span class="text-danger">*</span></label>
                    <select name="type" class="form-select" required>
                        <option value="">Sélectionnez un service...</option>
                        <option value="securite">Sécurité Gérée (Surveillance, antivirus, pare-feu)</option>
                        <option value="maintenance">Maintenance de Parc (Ordis, imprimantes, réseau)</option>
                        <option value="developpement">Digitalisation & Développement (Site, App, GED)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Titre de la demande <span class="text-danger">*</span></label>
                    <input type="text" name="subject" class="form-control" placeholder="Ex: Maintenance mensuelle de nos 15 postes" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description du besoin <span class="text-danger">*</span></label>
                    <textarea name="description" class="form-control" rows="4" placeholder="Décrivez votre besoin en détail..." required></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Périmètre / Volume estimé</label>
                    <input type="text" name="volume" class="form-control" placeholder="Ex: 10 ordinateurs, 2 serveurs...">
                </div>

                <button type="submit" class="btn btn-primary w-100">Soumettre la demande</button>
            </form>
        </div>
    </div>
</div>
@endsection
