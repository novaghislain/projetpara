@extends('layouts.gel-direction')

@section('title', 'Supervision des Clients — Direction')

@section('page_title', 'Clients & Chiffre d\'Affaires')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:24px;">
    <div>
        <h1 style="font-size:24px; font-weight:700; color:var(--dir-primary); margin:0;">Supervision des Clients</h1>
        <p style="color:var(--dir-text-muted); font-size:14px; margin:4px 0 0 0;">Visualisez l'état du portefeuille clients du cabinet.</p>
    </div>
    <form method="GET" action="{{ route('gel-direction.clients.index') }}" style="display:flex; gap:10px;">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher une entreprise..." class="form-control" style="width:250px; border-radius:8px;">
        <button type="submit" class="btn btn-primary" style="background:var(--dir-primary); border:none; border-radius:8px;">
            <i class="fas fa-search"></i>
        </button>
    </form>
</div>

<div style="background:white; border-radius:12px; border:1px solid var(--dir-border); box-shadow:0 2px 4px rgba(0,0,0,0.02); overflow:hidden;">
    <table style="width:100%; border-collapse:collapse;">
        <thead>
            <tr style="background:#F8FAFC; border-bottom:1px solid var(--dir-border); text-align:left;">
                <th style="padding:16px 20px; font-size:12px; font-weight:600; color:var(--dir-text-muted); text-transform:uppercase; letter-spacing:0.5px;">Entreprise</th>
                <th style="padding:16px 20px; font-size:12px; font-weight:600; color:var(--dir-text-muted); text-transform:uppercase; letter-spacing:0.5px;">Contact</th>
                <th style="padding:16px 20px; font-size:12px; font-weight:600; color:var(--dir-text-muted); text-transform:uppercase; letter-spacing:0.5px;">Statut</th>
                <th style="padding:16px 20px; font-size:12px; font-weight:600; color:var(--dir-text-muted); text-transform:uppercase; letter-spacing:0.5px; text-align:right;">CA Mensuel (Est.)</th>
                <th style="padding:16px 20px;"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($clients as $client)
            <tr style="border-bottom:1px solid #F1F5F9; transition:background 0.2s;">
                <td style="padding:16px 20px;">
                    <div style="font-weight:600; color:var(--dir-text); font-size:14px;">{{ $client->nom_entreprise }}</div>
                    <div style="font-size:12px; color:var(--dir-text-muted); margin-top:2px;">Créé le {{ $client->created_at->format('d/m/Y') }}</div>
                </td>
                <td style="padding:16px 20px; font-size:13px; color:var(--dir-text);">
                    <i class="fas fa-envelope text-muted me-1"></i> {{ $client->email_contact ?? 'N/A' }}<br>
                    <i class="fas fa-phone text-muted me-1"></i> {{ $client->telephone ?? 'N/A' }}
                </td>
                <td style="padding:16px 20px;">
                    <span style="background:#ECFDF5; color:#10B981; font-size:11px; font-weight:600; padding:4px 8px; border-radius:12px;">Actif</span>
                </td>
                <td style="padding:16px 20px; font-size:14px; font-weight:600; color:var(--dir-text); text-align:right;">
                    {{ number_format(rand(1000, 5000), 0, ',', ' ') }} €
                </td>
                <td style="padding:16px 20px; text-align:right;">
                    <a href="{{ route('gel-direction.clients.show', $client->id) }}" class="btn btn-sm" style="background:#EFF6FF; color:#3B82F6; font-weight:600; border-radius:6px; font-size:12px;">
                        Vue 360° <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="padding:40px; text-align:center; color:var(--dir-text-muted);">
                    <i class="fas fa-building fa-2x mb-3" style="opacity:0.5;"></i>
                    <p>Aucun client trouvé.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    <div style="padding:16px 20px; border-top:1px solid var(--dir-border);">
        {{ $clients->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
