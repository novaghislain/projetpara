@php $currentSection = 'depenses'; @endphp
@extends('layouts.gel-business')
@section('title', 'Bons de commande - GEL Business')
@section('content')
<div class="gel-page-header">
    <div><h1 class="gel-page-title">Bons de commande</h1><p class="gel-page-subtitle">Suivi des bons de commande</p></div>
    <button class="gel-btn gel-btn-primary gel-btn-sm"><i class="fas fa-plus"></i> Nouveau bon de commande</button>
</div>
<div class="gel-card"><div class="gel-card-body">
    <div class="gel-empty">
        <i class="fas fa-shopping-cart"></i>
        <h3>Aucun bon de commande</h3>
        <p>Créez des bons de commande pour vos achats.</p>
    </div>
</div></div>
@endsection
