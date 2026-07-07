@php $currentSection = 'depenses'; @endphp
@extends('layouts.gel-business')
@section('title', 'Dépenses - GEL Business')
@section('content')
<div class="gel-page-header">
    <div><h1 class="gel-page-title">Dépenses</h1><p class="gel-page-subtitle">Toutes les dépenses</p></div>
    <div style="display:flex;gap:8px;">
        <button class="gel-btn gel-btn-secondary gel-btn-sm"><i class="fas fa-filter"></i> Filtrer</button>
        <button class="gel-btn gel-btn-primary gel-btn-sm"><i class="fas fa-plus"></i> Nouvelle dépense</button>
    </div>
</div>
<div class="gel-card">
    <div class="gel-card-body">
        <div class="gel-empty">
            <i class="fas fa-money-bill-wave"></i>
            <h3>Aucune dépense</h3>
            <p>Les dépenses apparaîtront ici.</p>
        </div>
    </div>
</div>
@endsection
