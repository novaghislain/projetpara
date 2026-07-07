@php $currentSection = 'ventes'; @endphp
@extends('layouts.gel-business')
@section('title', 'Clients - GEL Business')
@section('content')
<div class="gel-page-header">
    <div><h1 class="gel-page-title">Clients</h1><p class="gel-page-subtitle">Votre portefeuille clients</p></div>
    <button class="gel-btn gel-btn-primary gel-btn-sm"><i class="fas fa-plus"></i> Nouveau client</button>
</div>
<div class="gel-card">
    <div class="gel-card-body" style="padding:0;">
        <div class="gel-empty">
            <i class="fas fa-users"></i>
            <h3>Aucun client</h3>
            <p>Ajoutez vos clients pour commencer.</p>
        </div>
    </div>
</div>
@endsection
