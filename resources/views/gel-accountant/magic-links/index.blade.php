@extends('layouts.gel-accountant')
@section('title', 'Magic Links — Demandes de Documents')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-magic" style="color:var(--gel-primary); margin-right:8px;"></i> Magic Links</h1>
        <p class="gel-page-subtitle">Envoyez des liens sécurisés à vos clients pour collecter des documents sans login.</p>
    </div>
    <div>
        <a href="{{ route('gel-accountant.magic-links.create') }}" class="gel-btn gel-btn-primary">
            <i class="fas fa-plus"></i> Nouvelle Demande
        </a>
    </div>
</div>

@if(session('success'))
<div class="gel-alert gel-alert-success" style="background:rgba(16,185,129,0.1); border:1px solid #10b981; border-radius:8px; padding:12px 16px; margin-bottom:20px; color:#065f46; word-break:break-all;">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

<div class="gel-card" style="overflow:hidden;">
    <table style="width:100%; border-collapse:collapse; font-size:13px;">
        <thead style="background:var(--gel-sidebar-bg); color:white;">
            <tr>
                <th style="padding:10px 14px; text-align:left;">Titre</th>
                <th style="padding:10px 14px; text-align:left;">Client</th>
                <th style="padding:10px 14px; text-align:center;">Canal</th>
                <th style="padding:10px 14px; text-align:center;">Statut</th>
                <th style="padding:10px 14px; text-align:center;">Expiration</th>
                <th style="padding:10px 14px; text-align:center;">Lien Public</th>
            </tr>
        </thead>
        <tbody>
            @forelse($links as $link)
            @php
                $statusConfig = [
                    'sent'      => ['color'=>'#3b82f6','bg'=>'rgba(59,130,246,0.1)','icon'=>'fa-paper-plane','label'=>'Envoyé'],
                    'viewed'    => ['color'=>'#f59e0b','bg'=>'rgba(245,158,11,0.1)','icon'=>'fa-eye','label'=>'Vu'],
                    'responded' => ['color'=>'#10b981','bg'=>'rgba(16,185,129,0.1)','icon'=>'fa-check-circle','label'=>'Répondu'],
                    'expired'   => ['color'=>'#ef4444','bg'=>'rgba(239,68,68,0.1)','icon'=>'fa-times-circle','label'=>'Expiré'],
                ];
                $s = $statusConfig[$link->status] ?? $statusConfig['sent'];
            @endphp
            <tr style="border-bottom:1px solid var(--gel-border);">
                <td style="padding:10px 14px; font-weight:600;">{{ $link->title }}</td>
                <td style="padding:10px 14px;">{{ $link->client?->company_name ?? '—' }}</td>
                <td style="padding:10px 14px; text-align:center;">
                    <span style="font-size:12px; text-transform:uppercase; font-weight:600; color:var(--gel-text-muted);">{{ $link->channel }}</span>
                </td>
                <td style="padding:10px 14px; text-align:center;">
                    <span style="display:inline-flex; align-items:center; gap:5px; padding:3px 10px; border-radius:999px; font-size:12px; font-weight:600; color:{{ $s['color'] }}; background:{{ $s['bg'] }};">
                        <i class="fas {{ $s['icon'] }}"></i> {{ $s['label'] }}
                    </span>
                </td>
                <td style="padding:10px 14px; text-align:center; font-size:12px; color:var(--gel-text-muted);">
                    {{ $link->expires_at?->format('d/m/Y') ?? '—' }}
                </td>
                <td style="padding:10px 14px; text-align:center;">
                    @if($link->status !== 'expired')
                    <button onclick="copyLink('{{ route('magic-link.public', $link->token) }}')" class="gel-btn gel-btn-secondary" style="font-size:11px; padding:4px 10px;">
                        <i class="fas fa-copy"></i> Copier
                    </button>
                    @else
                    <span style="color:var(--gel-text-muted); font-size:12px;">Expiré</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="padding:40px; text-align:center; color:var(--gel-text-muted);">
                    <i class="fas fa-magic" style="font-size:2rem; margin-bottom:10px; display:block; opacity:0.3;"></i>
                    Aucune demande créée. Créez votre premier Magic Link !
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div style="padding:12px 16px;">{{ $links->links() }}</div>
</div>

<script>
function copyLink(url) {
    navigator.clipboard.writeText(url).then(() => {
        if (window.showGelToast) window.showGelToast('Lien copié !');
        else alert('Lien copié : ' + url);
    });
}
</script>
@endsection
