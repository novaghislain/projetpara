@php $currentSection = 'ventes'; @endphp
@extends('layouts.gel-business')
@section('title', 'Devis - GEL Business')
@section('content')
<div class="gel-page-header">
    <div><h1 class="gel-page-title">Devis</h1><p class="gel-page-subtitle">Gestion des devis</p></div>
    <button class="gel-btn gel-btn-primary gel-btn-sm"><i class="fas fa-plus"></i> Nouveau devis</button>
</div>
<div class="gel-card"><div class="gel-card-body">
    <div class="gel-empty">
        <i class="fas fa-file-invoice"></i>
        <h3>Aucun devis</h3>
        <p>Créez vos premiers devis pour vos clients.</p>
    </div>
</div></div>
@endsection
