{{-- ============================================ --}}
{{-- PAGE : Création d'un produit/service          --}}
{{-- ============================================ --}}

@php $currentSection = 'ventes'; @endphp

{{-- === HERITAGE === --}}
@extends('layouts.gel-business')

{{-- === TITRE === --}}
@section('title', 'Nouveau produit - GEL Business')

{{-- === CONTENU === --}}
@section('content')
<div class="gel-page-header">
    <div><h1 class="gel-page-title">Nouveau produit</h1><p class="gel-page-subtitle">Ajoutez un produit ou service</p></div>
    <a href="{{ route('gel-business.ventes.produits') }}" class="gel-btn gel-btn-secondary gel-btn-sm"><i class="fas fa-arrow-left"></i> Retour</a>
</div>
<div class="gel-card">
    <div class="gel-card-body">
        @include('partials._form-errors')
        <form method="POST" action="{{ route('gel-business.ventes.produits.store') }}">
            @csrf
            <div class="gel-form-group">
                <label>Nom *</label>
                <input type="text" name="nom" class="gel-form-control" required placeholder="Nom du produit">
            </div>
            <div class="gel-form-group">
                <label>Prix unitaire *</label>
                <input type="number" name="prix" class="gel-form-control" required min="0" step="0.01" placeholder="0">
            </div>
            <div class="gel-form-group">
                <label>Description</label>
                <textarea name="description" class="gel-form-control" rows="3" placeholder="Description du produit/service"></textarea>
            </div>
            <button type="submit" class="gel-btn gel-btn-primary">Enregistrer</button>
        </form>
    </div>
</div>
@endsection

