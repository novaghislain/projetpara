@extends('layouts.gel-accountant')
@section('title', 'Vue d\'ensemble du client - GEL Cabinet')

@section('content')
<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">Vue d'ensemble du client</h1>
        <p class="gel-page-subtitle">Statistiques et état global du dossier client</p>
    </div>
</div>

<div class="gel-card p-4 mb-4">
    <div class="gel-card-body p-4 mb-4">
        <div class="gel-empty" style="padding:60px 40px;">
            <i class="fas fa-eye" style="font-size:48px;color:var(--gel-primary);margin-bottom:16px;display:block;"></i>
            <h3>Aperçu du dossier</h3>
            <p>Sélectionnez un client dans la liste pour voir sa vue d'ensemble complète.</p>
            <a href="{{ route('gel-accountant.clients') }}" class="gel-btn gel-btn-primary" style="margin-top:16px;">
                <i class="fas fa-users"></i> Retour aux clients
            </a>
        </div>
    </div>
</div>
@endsection

