@extends('layouts.gel-accountant')
@section('title', 'Dossiers de Travail (Workpapers)')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-clipboard-check" style="color:var(--gel-primary); margin-right:8px;"></i> Dossiers de Travail</h1>
        <p class="gel-page-subtitle">Révisez chaque compte avant la clôture — Période : <strong>{{ $period }}</strong> (comparaison N vs N-1)</p>
    </div>
    <div style="display:flex; gap:10px; align-items:center;">
        <form method="GET" style="display:flex; gap:8px;">
            <input type="number" name="period" value="{{ $period }}" class="gel-form-control" style="width:100px;" placeholder="Année" min="2020" max="2030">
            <button type="submit" class="gel-btn gel-btn-secondary"><i class="fas fa-search"></i></button>
        </form>
    </div>
</div>

{{-- Barre de progression --}}
<div class="gel-card p-4 mb-4">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
        <div>
            <span style="font-weight:700; font-size:24px; color:var(--gel-primary);">{{ $progress_pct }}%</span>
            <span style="color:var(--gel-text-muted); margin-left:8px;">des comptes révisés</span>
        </div>
        <div style="display:flex; gap:20px; font-size:13px;">
            <span><i class="fas fa-check-circle" style="color:#10b981;"></i> <strong>{{ $reviewed }}</strong> Révisés</span>
            <span><i class="fas fa-clock" style="color:#f59e0b;"></i> <strong>{{ $in_progress }}</strong> En cours</span>
            <span><i class="fas fa-circle" style="color:#e5e7eb;"></i> <strong>{{ $total - $reviewed - $in_progress }}</strong> Non révisés</span>
        </div>
    </div>
    <div style="background:#e5e7eb; border-radius:999px; height:10px; overflow:hidden;">
        <div style="width:{{ $progress_pct }}%; background:linear-gradient(90deg, var(--gel-primary), #10b981); height:100%; border-radius:999px; transition:width 0.5s ease;"></div>
    </div>
</div>

{{-- Liste des comptes --}}
<div class="gel-card" style="overflow:hidden;">
    <table style="width:100%; border-collapse:collapse; font-size:13px;">
        <thead style="background:var(--gel-sidebar-bg); color:white;">
            <tr>
                <th style="padding:10px 14px; text-align:left;">Code</th>
                <th style="padding:10px 14px; text-align:left;">Intitulé du Compte</th>
                <th style="padding:10px 14px; text-align:right;">Solde N ({{ $period }})</th>
                <th style="padding:10px 14px; text-align:right;">Solde N-1 ({{ $prevPeriod }})</th>
                <th style="padding:10px 14px; text-align:right;">Variation</th>
                <th style="padding:10px 14px; text-align:center;">Statut</th>
                <th style="padding:10px 14px; text-align:center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($accounts as $account)
            <tr style="border-bottom:1px solid var(--gel-border);" id="row-{{ $account->id }}">
                <td style="padding:10px 14px; font-weight:600; font-family:monospace;">{{ $account->code }}</td>
                <td style="padding:10px 14px;">{{ $account->name }}</td>
                <td style="padding:10px 14px; text-align:right; font-family:monospace; {{ $account->balance_n < 0 ? 'color:#dc3545;' : '' }}">
                    {{ number_format($account->balance_n, 0, ',', ' ') }}
                </td>
                <td style="padding:10px 14px; text-align:right; font-family:monospace; {{ $account->balance_n1 < 0 ? 'color:#dc3545;' : '' }}">
                    {{ number_format($account->balance_n1, 0, ',', ' ') }}
                </td>
                <td style="padding:10px 14px; text-align:right; font-family:monospace; {{ abs($account->variation) >= 1000000 ? 'color:#f59e0b; font-weight:700;' : '' }}">
                    @php $sign = $account->variation > 0 ? '+' : ''; @endphp
                    {{ $sign }}{{ number_format($account->variation, 0, ',', ' ') }}
                </td>
                <td style="padding:10px 14px; text-align:center;">
                    @php
                        $status = $account->wp_status;
                        $badges = [
                            'reviewed'     => ['color'=>'#10b981','bg'=>'rgba(16,185,129,0.1)','icon'=>'fa-check-circle','label'=>'Révisé'],
                            'in_progress'  => ['color'=>'#f59e0b','bg'=>'rgba(245,158,11,0.1)','icon'=>'fa-clock','label'=>'En cours'],
                            'not_reviewed' => ['color'=>'#9ca3af','bg'=>'rgba(156,163,175,0.1)','icon'=>'fa-circle','label'=>'Non révisé'],
                        ];
                        $b = $badges[$status] ?? $badges['not_reviewed'];
                    @endphp
                    <span id="badge-{{ $account->id }}" style="display:inline-flex; align-items:center; gap:5px; padding:3px 10px; border-radius:999px; font-size:12px; font-weight:600; color:{{ $b['color'] }}; background:{{ $b['bg'] }};">
                        <i class="fas {{ $b['icon'] }}"></i> {{ $b['label'] }}
                    </span>
                </td>
                <td style="padding:10px 14px; text-align:center;">
                    <div style="display:flex; gap:6px; justify-content:center;">
                        <button onclick="setStatus({{ $account->id }}, 'in_progress')" title="En cours" style="border:none; background:rgba(245,158,11,0.15); color:#f59e0b; border-radius:6px; padding:4px 8px; cursor:pointer; font-size:12px;"><i class="fas fa-clock"></i></button>
                        <button onclick="setStatus({{ $account->id }}, 'reviewed')" title="Marquer révisé" style="border:none; background:rgba(16,185,129,0.15); color:#10b981; border-radius:6px; padding:4px 8px; cursor:pointer; font-size:12px;"><i class="fas fa-check"></i></button>
                        <button onclick="setStatus({{ $account->id }}, 'not_reviewed')" title="Réinitialiser" style="border:none; background:rgba(156,163,175,0.15); color:#9ca3af; border-radius:6px; padding:4px 8px; cursor:pointer; font-size:12px;"><i class="fas fa-undo"></i></button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="padding:40px; text-align:center; color:var(--gel-text-muted);">
                    <i class="fas fa-inbox" style="font-size:2rem; margin-bottom:10px; display:block;"></i>
                    Aucun compte trouvé. Importez d'abord un plan comptable SYSCOHADA.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
const period = '{{ $period }}';
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

const badgeConfig = {
    reviewed:     { color: '#10b981', bg: 'rgba(16,185,129,0.1)',   icon: 'fa-check-circle', label: 'Révisé' },
    in_progress:  { color: '#f59e0b', bg: 'rgba(245,158,11,0.1)',   icon: 'fa-clock',        label: 'En cours' },
    not_reviewed: { color: '#9ca3af', bg: 'rgba(156,163,175,0.1)',  icon: 'fa-circle',       label: 'Non révisé' },
};

function setStatus(accountId, status) {
    fetch(`/gel-accountant/workpapers/${accountId}/status`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ status, period }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const b = badgeConfig[data.status] || badgeConfig.not_reviewed;
            const badge = document.getElementById(`badge-${accountId}`);
            badge.style.color = b.color;
            badge.style.background = b.bg;
            badge.innerHTML = `<i class="fas ${b.icon}"></i> ${b.label}`;
        }
    })
    .catch(() => alert('Erreur lors de la mise à jour.'));
}
</script>
@endsection
