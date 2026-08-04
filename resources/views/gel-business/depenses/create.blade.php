{{-- ============================================ --}}
{{-- PAGE : Création d'une dépense                --}}
{{-- ============================================ --}}

@php $currentSection = 'depenses'; @endphp

{{-- === HERITAGE === --}}
@extends('layouts.gel-business')

{{-- === TITRE === --}}
@section('title', 'Nouvelle dépense - GEL Business')

{{-- === CONTENU === --}}
@section('content')
<div class="gel-page-header">
    <div><h1 class="gel-page-title">Nouvelle dépense</h1><p class="gel-page-subtitle">Enregistrez une dépense</p></div>
    <a href="{{ route('gel-business.depenses.index') }}" class="gel-btn gel-btn-secondary gel-btn-sm"><i class="fas fa-arrow-left"></i> Retour</a>
</div>
<div class="gel-card">
    <div class="gel-card-body">
        @include('partials._form-errors')
        <form method="POST" action="{{ route('gel-business.depenses.store') }}">
            @csrf
            <div class="gel-form-group">
                <label>Libellé *</label>
                <input type="text" name="libelle" class="gel-form-control" required placeholder="Libellé de la dépense">
            </div>
            <div class="gel-form-group">
                <label>Montant *</label>
                <input type="number" name="montant" class="gel-form-control" required min="0" step="0.01" placeholder="0">
            </div>
            <div class="gel-form-group">
                <label>Date *</label>
                <input type="date" name="date_depense" class="gel-form-control" required value="{{ date('Y-m-d') }}">
            </div>
            <button type="submit" class="gel-btn gel-btn-primary">Enregistrer</button>
        </form>
    </div>
</div>
@endsection

