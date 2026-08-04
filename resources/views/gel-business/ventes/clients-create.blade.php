{{-- ============================================ --}}
{{-- PAGE : Création d'un nouveau client           --}}
{{-- ============================================ --}}

@php $currentSection = 'ventes'; @endphp

{{-- === HERITAGE === --}}
@extends('layouts.gel-business')

{{-- === TITRE === --}}
@section('title', 'Nouveau client - GEL Business')

{{-- === CONTENU === --}}
@section('content')
<div class="gel-page-header">
    <div><h1 class="gel-page-title">Nouveau client</h1><p class="gel-page-subtitle">Ajoutez un nouveau client</p></div>
    <a href="{{ route('gel-business.ventes.clients') }}" class="gel-btn gel-btn-secondary gel-btn-sm"><i class="fas fa-arrow-left"></i> Retour</a>
</div>
<div class="gel-card">
    <div class="gel-card-body">
        @include('partials._form-errors')
        <form method="POST" action="{{ route('gel-business.ventes.clients.store') }}">
            @csrf
            <div class="gel-form-group">
                <label>Nom *</label>
                <input type="text" name="name" class="gel-form-control" required placeholder="Nom du client">
            </div>
            <div class="gel-form-group">
                <label>Email</label>
                <input type="email" name="email" class="gel-form-control" placeholder="client@exemple.com">
            </div>
            <div class="gel-form-group">
                <label>Téléphone</label>
                <input type="text" name="phone" class="gel-form-control" placeholder="+229 XX XX XX XX">
            </div>
            <button type="submit" class="gel-btn gel-btn-primary">Enregistrer</button>
        </form>
    </div>
</div>
@endsection

