{{-- ============================================ --}}
{{-- PAGE : Création d'un devis                    --}}
{{-- ============================================ --}}

@php $currentSection = 'ventes'; @endphp

{{-- === HERITAGE === --}}
@extends('layouts.gel-business')

{{-- === TITRE === --}}
@section('title', 'Nouveau devis - GEL Business')

{{-- === CONTENU === --}}
@section('content')
<div class="gel-page-header">
    <div><h1 class="gel-page-title">Nouveau devis</h1><p class="gel-page-subtitle">Créez un nouveau devis</p></div>
    <a href="{{ route('gel-business.ventes.devis') }}" class="gel-btn gel-btn-secondary gel-btn-sm"><i class="fas fa-arrow-left"></i> Retour</a>
</div>
<div class="gel-card">
    <div class="gel-card-body">
        @include('partials._form-errors')
        <form method="POST" action="{{ route('gel-business.ventes.devis.store') }}">
            @csrf
            <div class="gel-form-group">
                <label>Client *</label>
                <input type="text" name="client_nom" class="gel-form-control" required placeholder="Nom du client">
            </div>
            <div class="gel-form-group">
                <label>Montant estimé *</label>
                <input type="number" name="montant" class="gel-form-control" required min="0" step="0.01" placeholder="0">
            </div>
            <div class="gel-form-group">
                <label>Date du devis *</label>
                <input type="date" name="date_devis" class="gel-form-control" required value="{{ date('Y-m-d') }}">
            </div>
            <button type="submit" class="gel-btn gel-btn-primary">Enregistrer</button>
        </form>
    </div>
</div>
@endsection

