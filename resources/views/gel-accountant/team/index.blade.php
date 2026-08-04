@php $currentSection = 'team'; @endphp
@extends('layouts.gel-accountant')
@section('title', 'Team - GEL Accountant')
@section('content')
<div class="gel-page-header">
    <div><h1 class="gel-page-title">Team</h1><p class="gel-page-subtitle">Gestion de l'équipe</p></div>
    <button class="gel-btn gel-btn-primary gel-btn-sm"><i class="fas fa-plus"></i> Inviter</button>
</div>
<div class="gel-card p-4 mb-4">
    <div class="gel-card-body p-4 mb-4">
        <div class="gel-empty">
            <i class="fas fa-user-friends"></i>
            <h3>Votre équipe</h3>
            <p>Invitez des collaborateurs pour gérer les clients.</p>
        </div>
    </div>
</div>
@endsection
