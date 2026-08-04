{{-- ============================================ --}}
{{-- PAGE : Création d'un rapprochement bancaire   --}}
{{-- ============================================ --}}

@php $currentSection = 'banque'; @endphp

{{-- === HERITAGE === --}}
@extends('layouts.gel-business')

{{-- === TITRE === --}}
@section('title', 'Nouveau rapprochement - GEL Business')

{{-- === CONTENU === --}}
@section('content')
<div class="gel-page-header">
    <div><h1 class="gel-page-title">Nouveau rapprochement</h1><p class="gel-page-subtitle">Rapprochez vos relevés bancaires</p></div>
    <a href="{{ route('gel-business.banque.rapprochement') }}" class="gel-btn gel-btn-secondary gel-btn-sm"><i class="fas fa-arrow-left"></i> Retour</a>
</div>
<div class="gel-card">
    <div class="gel-card-body">
        @include('partials._form-errors')
        <form method="POST" action="{{ route('gel-business.banque.rapprochement.store') }}">
            @csrf
            <div class="gel-form-group">
                <label>Date rapprochement *</label>
                <input type="date" name="date_rapprochement" class="gel-form-control" required value="{{ date('Y-m-d') }}">
            </div>
            <div class="gel-form-group">
                <label>Solde bancaire *</label>
                <input type="number" name="solde_bancaire" class="gel-form-control" required min="0" step="0.01" placeholder="0">
            </div>
            <button type="submit" class="gel-btn gel-btn-primary">Enregistrer</button>
        </form>
    </div>
</div>
@endsection

