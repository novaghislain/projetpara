@extends('layouts.gel-informaticien')

@section('title', 'Projet #' . $devRequest->id)

@section('content')
<div class="p-4">
    <div class="animate-fade" style="border-bottom: 1px solid var(--sec-border); padding-bottom: 12px; margin-bottom: 24px; display:flex; justify-content:space-between; align-items:flex-end;">
        <div>
            <h1 style="font-size: 18px; font-weight: 700; color: var(--sec-text-primary); margin-bottom: 4px;">Projet #{{ $devRequest->id }}</h1>
            <a href="{{ route('gel-informaticien.dev-requests.index') }}" class="btn btn-sm btn-light mt-2"><i class="fas fa-arrow-left"></i> Retour aux projets</a>
        </div>
    </div>

    <div class="row g-4 animate-fade delay-1">
        <div class="col-lg-8">
            <div class="pro-panel mb-4" style="border-top: 4px solid var(--sec-info);">
                <div class="sec-card-header" style="padding:16px 20px; border-bottom:1px solid var(--sec-border);">
                    <h5 class="mb-0" style="font-weight:600; font-size:15px; color:var(--sec-info);"><i class="fas fa-file-alt me-2"></i> Expression de besoin</h5>
                </div>
                <div class="panel-body" style="padding:20px;">
                <h4 class="mb-3" style="color:var(--sec-text);">{{ $devRequest->subject }}</h4>
                <div class="p-4 rounded" style="background:#F8FAFC; color:var(--sec-text); font-size: 1.05rem; line-height: 1.6; border:1px solid var(--sec-border);">
                    {!! nl2br(e($devRequest->description)) !!}
                </div>
            </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="pro-panel mb-4">
            <div class="sec-card-header" style="padding:16px 20px; border-bottom:1px solid var(--sec-border);">
                <h6 class="mb-0" style="font-weight:600; font-size:15px;"><i class="fas fa-tasks me-2" style="color:var(--sec-info);"></i> Gestion du Projet</h6>
            </div>
            <div class="panel-body" style="padding:20px;">
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
                    
                    <button type="submit" class="sec-btn w-100 justify-content-center" style="background:var(--sec-info); color:white; font-weight:600;">Mettre à jour le projet</button>
                </form>
            </div>
        </div>
    </div>
</div>
</div>
@endsection
