@extends('layouts.portal')
@section('title', 'Détails Campagne - ' . $campaign->name)

@section('content')
<div class="portal-mb-6">
    <a href="{{ route('client.marketing.index', ['slug' => $slug]) }}" style="text-decoration:none; color:var(--portal-text-secondary); font-size:13px;">
        <i class="fas fa-arrow-left"></i> Retour au Marketing
    </a>
</div>

<div class="portal-page-header portal-mb-6">
    <h1 class="portal-title">{{ $campaign->name }}</h1>
    <p class="portal-subtitle">Type : {{ ucfirst(str_replace('_', ' ', $campaign->type)) }} | Statut : {{ $campaign->status }}</p>
</div>

@if(session('success'))
    <div class="portal-alert portal-alert-success">{{ session('success') }}</div>
@endif

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 32px;">
    <!-- Visuels et Validation -->
    <div class="portal-card" style="background:white; border:1px solid var(--portal-border); border-radius:12px; padding:20px;">
        <h2 style="font-size:16px; margin-bottom:15px; font-weight:700;">Visuels et Livrables de la campagne</h2>
        
        @forelse($campaign->contents as $content)
            <div style="border:1px solid #E5E7EB; border-radius:8px; padding:16px; margin-bottom:16px;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                    <div>
                        <h4 style="margin:0 0 5px; font-size:15px;">{{ $content->title }}</h4>
                        <p style="margin:0 0 10px; font-size:13px; color:var(--portal-text-secondary);">{{ $content->description }}</p>
                    </div>
                    <div>
                        @if($content->status == 'pending_client')
                            <span style="background:#FFFBEB; color:#F59E0B; padding:4px 8px; border-radius:4px; font-size:11px; font-weight:600;">En attente de validation</span>
                        @elseif($content->status == 'approved')
                            <span style="background:#ECFDF5; color:#10B981; padding:4px 8px; border-radius:4px; font-size:11px; font-weight:600;">Approuvé</span>
                        @elseif($content->status == 'rejected')
                            <span style="background:#FEF2F2; color:#EF4444; padding:4px 8px; border-radius:4px; font-size:11px; font-weight:600;">Rejeté</span>
                        @else
                            <span style="background:#F3F4F6; color:#6B7280; padding:4px 8px; border-radius:4px; font-size:11px; font-weight:600;">Brouillon</span>
                        @endif
                    </div>
                </div>

                <div style="margin-top:15px; background:#F9FAFB; padding:12px; border-radius:6px; display:flex; align-items:center; gap:10px;">
                    <i class="fas fa-file-image" style="font-size:24px; color:var(--portal-accent);"></i>
                    <div style="flex:1;">
                        <div style="font-size:13px; font-weight:600;">Aperçu du livrable (Demo)</div>
                    </div>
                    <a href="#" class="portal-btn portal-btn-secondary" style="padding:4px 10px; font-size:12px;">Voir</a>
                </div>

                @if($content->status == 'pending_client')
                    <div style="margin-top:15px; border-top:1px solid #E5E7EB; padding-top:15px;">
                        <h5 style="margin:0 0 10px; font-size:13px; font-weight:600;">Votre décision</h5>
                        <form action="{{ route('gel-communication.contents.feedback', $content->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" id="status_{{ $content->id }}" value="">
                            <textarea name="client_feedback" class="portal-input" rows="2" placeholder="Ajouter un commentaire (obligatoire si rejeté)..." style="margin-bottom:10px;"></textarea>
                            <div style="display:flex; gap:10px;">
                                <button type="submit" onclick="document.getElementById('status_{{ $content->id }}').value='approved'" class="portal-btn" style="background:#10B981; color:white; border:none; flex:1;">
                                    <i class="fas fa-check"></i> Approuver
                                </button>
                                <button type="submit" onclick="document.getElementById('status_{{ $content->id }}').value='rejected'" class="portal-btn" style="background:#EF4444; color:white; border:none; flex:1;">
                                    <i class="fas fa-times"></i> Demander des retouches
                                </button>
                            </div>
                        </form>
                    </div>
                @elseif($content->client_feedback)
                    <div style="margin-top:15px; padding:10px; background:#F3F4F6; border-radius:6px; font-size:13px;">
                        <strong>Votre commentaire :</strong> {{ $content->client_feedback }}
                    </div>
                @endif
            </div>
        @empty
            <p style="color:var(--portal-text-secondary); font-size:14px; text-align:center; padding:30px 0;">Aucun visuel n'a encore été soumis pour cette campagne.</p>
        @endforelse
    </div>

    <!-- Statistiques -->
    <div class="portal-card" style="background:white; border:1px solid var(--portal-border); border-radius:12px; padding:20px;">
        <h2 style="font-size:16px; margin-bottom:15px; font-weight:700;">Performances</h2>
        
        @php
            $totalFollowers = $campaign->stats->sum('followers_gained');
            $totalReach = $campaign->stats->sum('reach');
            $totalSpend = $campaign->stats->sum('spend');
        @endphp

        <div style="display:flex; justify-content:space-between; margin-bottom:10px; border-bottom:1px solid #F3F4F6; padding-bottom:10px;">
            <span style="font-weight:600; font-size:13px; color:var(--portal-text-secondary);">Followers gagnés</span>
            <span style="font-weight:700; color:var(--portal-accent);">+{{ number_format($totalFollowers, 0, ',', ' ') }}</span>
        </div>
        <div style="display:flex; justify-content:space-between; margin-bottom:10px; border-bottom:1px solid #F3F4F6; padding-bottom:10px;">
            <span style="font-weight:600; font-size:13px; color:var(--portal-text-secondary);">Personnes touchées (Reach)</span>
            <span style="font-weight:700;">{{ number_format($totalReach, 0, ',', ' ') }}</span>
        </div>
        <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
            <span style="font-weight:600; font-size:13px; color:var(--portal-text-secondary);">Dépense Publicitaire</span>
            <span style="font-weight:700; color:var(--portal-danger);">{{ number_format($totalSpend, 0, ',', ' ') }} F</span>
        </div>

        <div style="margin-top:20px; font-size:12px; color:var(--portal-text-secondary); text-align:center;">
            Les statistiques sont mises à jour par notre équipe marketing en fonction des rapports des plateformes (Meta, Google, etc.).
        </div>
    </div>
</div>
@endsection
