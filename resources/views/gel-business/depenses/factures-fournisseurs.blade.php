@php $currentSection = 'depenses'; @endphp
@extends('layouts.gel-business')
@section('title', 'Factures fournisseurs - GEL Business')
@section('content')
<div class="gel-page-header">
    <div><h1 class="gel-page-title">Factures fournisseurs</h1><p class="gel-page-subtitle">Gestion des factures reçues</p></div>
    <button class="gel-btn gel-btn-primary gel-btn-sm"><i class="fas fa-plus"></i> Nouvelle facture</button>
</div>
<div class="gel-card"><div class="gel-card-body">
    <div class="gel-empty">
        <i class="fas fa-file-invoice"></i>
        <h3>Aucune facture fournisseur</h3>
        <p>Enregistrez les factures reçues de vos fournisseurs.</p>
    </div>
</div></div>
@endsection
