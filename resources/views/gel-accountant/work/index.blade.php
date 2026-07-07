@php $currentSection = 'work'; @endphp
@extends('layouts.gel-accountant')
@section('title', 'Work - GEL Accountant')
@section('content')
<div class="gel-page-header">
    <div><h1 class="gel-page-title">Work</h1><p class="gel-page-subtitle">Gestion des travaux en cours</p></div>
    <button class="gel-btn gel-btn-primary gel-btn-sm"><i class="fas fa-plus"></i> Nouveau travail</button>
</div>
<div class="gel-card">
    <div class="gel-card-body">
        <div class="gel-empty">
            <i class="fas fa-briefcase"></i>
            <h3>Aucun travail en cours</h3>
            <p>Les travaux apparaîtront ici une fois assignés.</p>
        </div>
    </div>
</div>
@endsection
