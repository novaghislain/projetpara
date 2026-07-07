@php $currentSection = 'depenses'; @endphp
@extends('layouts.gel-business')
@section('title', 'Fournisseurs - GEL Business')
@section('content')
<div class="gel-page-header">
    <div><h1 class="gel-page-title">Fournisseurs</h1><p class="gel-page-subtitle">Gestion des fournisseurs</p></div>
    <button class="gel-btn gel-btn-primary gel-btn-sm"><i class="fas fa-plus"></i> Nouveau fournisseur</button>
</div>
<div class="gel-card"><div class="gel-card-body">
    <div class="gel-empty">
        <i class="fas fa-truck"></i>
        <h3>Aucun fournisseur</h3>
        <p>Ajoutez vos fournisseurs pour suivre les achats.</p>
    </div>
</div></div>
@endsection
