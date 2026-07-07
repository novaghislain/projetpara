@php $currentSection = 'banque'; @endphp
@extends('layouts.gel-business')
@section('title', 'Rapprochement bancaire - GEL Business')
@section('content')
<div class="gel-page-header">
    <div><h1 class="gel-page-title">Rapprochement bancaire</h1><p class="gel-page-subtitle">Rapprochez vos relevés bancaires</p></div>
    <button class="gel-btn gel-btn-primary gel-btn-sm"><i class="fas fa-plus"></i> Nouveau rapprochement</button>
</div>
<div class="gel-card"><div class="gel-card-body">
    <div class="gel-empty">
        <i class="fas fa-exchange-alt"></i>
        <h3>Aucun rapprochement</h3>
        <p>Importez vos relevés bancaires pour rapprocher vos transactions.</p>
    </div>
</div></div>
@endsection
