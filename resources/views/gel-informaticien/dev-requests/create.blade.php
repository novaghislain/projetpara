@extends('layouts.gel-informaticien')

@section('title', 'Nouvelle Demande Dev')

@section('content')
<div class="p-4">
    <div class="animate-fade" style="border-bottom: 1px solid var(--sec-border); padding-bottom: 12px; margin-bottom: 24px; display:flex; justify-content:space-between; align-items:flex-end;">
        <div>
            <h1 style="font-size: 18px; font-weight: 700; color: var(--sec-text-primary); margin-bottom: 4px;">Nouvelle Demande de Développement</h1>
            <a href="{{ route('gel-informaticien.dev-requests.index') }}" class="btn btn-sm btn-light mt-2"><i class="fas fa-arrow-left"></i> Retour aux projets</a>
        </div>
    </div>

    <div class="row g-4 animate-fade delay-1">
        <div class="col-lg-8">
            <div class="pro-panel mb-4" style="border-top: 4px solid var(--sec-info);">
                <div class="sec-card-header" style="padding:16px 20px; border-bottom:1px solid var(--sec-border);">
                    <h5 class="mb-0" style="font-weight:600; font-size:15px; color:var(--sec-info);"><i class="fas fa-code me-2"></i> Créer un Projet Digital</h5>
                </div>
                <div class="panel-body" style="padding:20px;">
                    <form action="{{ route('gel-informaticien.dev-requests.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">Client</label>
                            <select name="client_id" class="form-select" required>
                                <option value="">-- Sélectionner un client --</option>
                                @foreach($clients as $c)
                                    <option value="{{ $c->id }}">{{ $c->company_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">Type de projet (Sujet)</label>
                            <input type="text" name="subject" class="form-control" required placeholder="Ex: Création site vitrine, Application de gestion...">
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold">Expression du besoin (Description)</label>
                            <textarea name="description" class="form-control" rows="6" required placeholder="Détaillez le besoin du client..."></textarea>
                        </div>

                        <button type="submit" class="sec-btn" style="background:var(--sec-info); color:white; font-weight:600;"><i class="fas fa-save me-1"></i> Créer la demande</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
