@extends('layouts.gel-direction')

@section('title', 'Supervision RH — Direction')

@section('page_title', 'Équipe & Ressources')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:24px;">
    <div>
        <h1 style="font-size:24px; font-weight:700; color:var(--dir-primary); margin:0;">Supervision des Équipes</h1>
        <p style="color:var(--dir-text-muted); font-size:14px; margin:4px 0 0 0;">Visualisez l'état des ressources humaines, la charge de travail et la productivité.</p>
    </div>
    <form method="GET" action="{{ route('gel-direction.team.index') }}" style="display:flex; gap:10px;">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher un collaborateur..." class="form-control" style="width:250px; border-radius:8px;">
        <button type="submit" class="btn btn-primary" style="background:var(--dir-primary); border:none; border-radius:8px;">
            <i class="fas fa-search"></i>
        </button>
    </form>
</div>

<div style="background:white; border-radius:12px; border:1px solid var(--dir-border); box-shadow:0 2px 4px rgba(0,0,0,0.02); overflow:hidden;">
    <table style="width:100%; border-collapse:collapse;">
        <thead>
            <tr style="background:#F8FAFC; border-bottom:1px solid var(--dir-border); text-align:left;">
                <th style="padding:16px 20px; font-size:12px; font-weight:600; color:var(--dir-text-muted); text-transform:uppercase; letter-spacing:0.5px;">Collaborateur</th>
                <th style="padding:16px 20px; font-size:12px; font-weight:600; color:var(--dir-text-muted); text-transform:uppercase; letter-spacing:0.5px;">Pôle / Rôle</th>
                <th style="padding:16px 20px; font-size:12px; font-weight:600; color:var(--dir-text-muted); text-transform:uppercase; letter-spacing:0.5px; text-align:center;">Tâches en cours</th>
                <th style="padding:16px 20px; font-size:12px; font-weight:600; color:var(--dir-text-muted); text-transform:uppercase; letter-spacing:0.5px; text-align:center;">Productivité (Mois)</th>
                <th style="padding:16px 20px;"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($team as $member)
            <tr style="border-bottom:1px solid #F1F5F9; transition:background 0.2s;">
                <td style="padding:16px 20px;">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <div style="width:36px; height:36px; border-radius:50%; background:#EFF6FF; color:#3B82F6; display:flex; align-items:center; justify-content:center; font-weight:700;">
                            {{ substr($member->name, 0, 1) }}
                        </div>
                        <div>
                            <div style="font-weight:600; color:var(--dir-text); font-size:14px;">{{ $member->name }}</div>
                            <div style="font-size:12px; color:var(--dir-text-muted); margin-top:2px;"><i class="fas fa-envelope text-muted"></i> {{ $member->email }}</div>
                        </div>
                    </div>
                </td>
                <td style="padding:16px 20px; font-size:13px; color:var(--dir-text);">
                    @if($member->is_accountant)
                        <span style="background:#EEF2FF; color:#4F46E5; font-size:11px; font-weight:600; padding:4px 8px; border-radius:12px;">Comptabilité</span>
                    @elseif($member->is_secretary)
                        <span style="background:#FDF4FF; color:#C026D3; font-size:11px; font-weight:600; padding:4px 8px; border-radius:12px;">Secrétariat</span>
                    @elseif($member->is_informaticien)
                        <span style="background:#ECFEFF; color:#0891B2; font-size:11px; font-weight:600; padding:4px 8px; border-radius:12px;">IT / Support</span>
                    @elseif($member->is_consultant)
                        <span style="background:#FFFBEB; color:#D97706; font-size:11px; font-weight:600; padding:4px 8px; border-radius:12px;">Consulting</span>
                    @else
                        <span style="background:#F1F5F9; color:#475569; font-size:11px; font-weight:600; padding:4px 8px; border-radius:12px;">Standard</span>
                    @endif
                </td>
                <td style="padding:16px 20px; text-align:center;">
                    <span style="font-size:16px; font-weight:700; color:var(--dir-text);">
                        {{ rand(2, 15) }}
                    </span>
                    <div style="font-size:11px; color:var(--dir-text-muted);">dossiers/tâches</div>
                </td>
                <td style="padding:16px 20px; text-align:center;">
                    <span style="font-size:16px; font-weight:700; color:#10B981;">
                        {{ rand(30, 95) }}%
                    </span>
                </td>
                <td style="padding:16px 20px; text-align:right;">
                    <a href="{{ route('gel-direction.team.show', $member->id) }}" class="btn btn-sm" style="background:#EFF6FF; color:#3B82F6; font-weight:600; border-radius:6px; font-size:12px;">
                        Profil & KPI <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="padding:40px; text-align:center; color:var(--dir-text-muted);">
                    <i class="fas fa-users fa-2x mb-3" style="opacity:0.5;"></i>
                    <p>Aucun collaborateur trouvé.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    <div style="padding:16px 20px; border-top:1px solid var(--dir-border);">
        {{ $team->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
