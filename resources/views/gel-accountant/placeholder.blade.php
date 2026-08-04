@extends('layouts.gel-accountant')

@section('title', $title ?? 'Module en construction')

@section('content')
<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">
            @if(isset($icon))
            <i class="fas {{ $icon }}" style="color:var(--gel-primary); margin-right:8px;"></i>
            @endif
            {{ $title ?? 'Module en construction' }}
        </h1>
        <p class="gel-page-subtitle">Cette fonctionnalité est actuellement en cours de développement.</p>
    </div>
    <div style="display:flex; gap:8px;">
        <a href="javascript:history.back()" class="gel-btn gel-btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>
</div>

<div class="gel-card p-4 mb-4" style="text-align: center;  margin-top: 20px;">
    <div style="width: 100px; height: 100px; background: var(--gel-primary-light); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; font-size: 40px; color: var(--gel-primary);">
        <i class="fas fa-tools"></i>
    </div>
    <h2 style="font-size: 22px; font-weight: 700; color: var(--gel-text-primary); margin-bottom: 12px;">Bientôt disponible !</h2>
    <p style="font-size: 15px; color: var(--gel-text-secondary); max-width: 500px; margin: 0 auto 24px; line-height: 1.6;">
        Le module <strong>{{ $title ?? 'demandé' }}</strong> est prévu dans la roadmap de développement et sera intégré très prochainement.
    </p>
    
    <a href="javascript:history.back()" class="gel-btn gel-btn-primary">
        <i class="fas fa-home"></i> Retourner À  la page précédente
    </a>
</div>
@endsection

