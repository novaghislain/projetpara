{{-- ============================================ --}}
{{-- PAGE : Création d'une facture client          --}}
{{-- ============================================ --}}

@php $currentSection = 'ventes'; @endphp

{{-- === HERITAGE === --}}
@extends('layouts.gel-business')

{{-- === TITRE === --}}
@section('title', 'Nouvelle facture - GEL Business')

{{-- === CONTENU === --}}
@section('content')
<div class="gel-page-header">
    <div><h1 class="gel-page-title">Nouvelle facture</h1><p class="gel-page-subtitle">Créez une nouvelle facture client</p></div>
    <a href="{{ route('gel-business.ventes.factures') }}" class="gel-btn gel-btn-secondary gel-btn-sm"><i class="fas fa-arrow-left"></i> Retour</a>
</div>
<div class="gel-card">
    <div class="gel-card-body">
        @include('partials._form-errors')
        <form method="POST" action="{{ route('gel-business.ventes.factures.store') }}">
            @csrf
            <div class="gel-form-group">
                <label>Client *</label>
                <input type="text" name="client_nom" class="gel-form-control" required placeholder="Nom du client">
            </div>
            <div class="gel-form-group">
                <label>Montant *</label>
                <input type="number" name="montant" class="gel-form-control" required min="0" step="0.01" placeholder="0">
            </div>
            <div class="gel-form-group">
                <label>Date facture *</label>
                <input type="date" name="date_facture" class="gel-form-control" required value="{{ date('Y-m-d') }}">
            </div>
            <button type="submit" class="gel-btn gel-btn-primary">Enregistrer</button>
        </form>
    </div>
</div>
@endsection

