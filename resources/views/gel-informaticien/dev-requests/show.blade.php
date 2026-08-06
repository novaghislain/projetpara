@extends('layouts.gel-informaticien')

@section('title', 'Projet #' . $devRequest->id)

@section('content')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="mb-0 text-info">Expression de besoin</h5>
            </div>
            <div class="card-body">
                <h4 class="mb-3">{{ $devRequest->subject }}</h4>
                <div class="p-4 bg-light rounded text-dark" style="font-size: 1.1rem; line-height: 1.6;">
                    {!! nl2br(e($devRequest->description)) !!}
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-0">
                <h6 class="mb-0">Gestion du Projet</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('gel-informaticien.dev-requests.update', $devRequest->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Statut du Projet</label>
                        <select name="status" class="form-select">
                            <option value="recue" {{ $devRequest->status == 'recue' ? 'selected' : '' }}>Demande reçue</option>
                            <option value="devis_en_cours" {{ $devRequest->status == 'devis_en_cours' ? 'selected' : '' }}>Devis en préparation</option>
                            <option value="en_developpement" {{ $devRequest->status == 'en_developpement' ? 'selected' : '' }}>En développement</option>
                            <option value="livre" {{ $devRequest->status == 'livre' ? 'selected' : '' }}>Projet Livré</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold">Lien vers le Devis / Contrat (URL)</label>
                        <input type="url" name="devis_url" class="form-control" value="{{ $devRequest->devis_url }}" placeholder="https://docs.google.com/...">
                        <div class="form-text">Ce lien sera partagé avec le client.</div>
                    </div>
                    
                    <button type="submit" class="btn btn-info text-white w-100 fw-bold">Mettre à jour le projet</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
