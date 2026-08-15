@extends('layouts.gel-secretary')
@section('title', 'Portefeuille Consolidé')

@section('content')
<div style="padding:20px 0;">
    <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:24px; border-bottom:1px solid #e2e8f0; padding-bottom:12px;">
        <div>
            <h1 style="font-size:24px; font-weight:800; color:#1e293b; margin:0;">Portefeuille Clients</h1>
            <p style="color:#64748b; margin:4px 0 0 0;">Vue consolidée de toutes les entreprises que vous gérez.</p>
        </div>
        <div>
            <a href="javascript:void(0)" onclick="alert('Module de création de client en construction.')" style="background:#0d9488; color:white; padding:10px 16px; border-radius:8px; font-weight:600; text-decoration:none;">
                <i class="fas fa-plus"></i> Nouveau Client
            </a>
        </div>
    </div>

    <div style="background:white; border-radius:12px; border:1px solid #e2e8f0; overflow:hidden;">
        <table style="width:100%; border-collapse:collapse; text-align:left;">
            <thead>
                <tr style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                    <th style="padding:12px 16px; font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;">Entreprise</th>
                    <th style="padding:12px 16px; font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;">Santé globale</th>
                    <th style="padding:12px 16px; font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;">Tâches en cours</th>
                    <th style="padding:12px 16px; font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;">Docs à traiter</th>
                    <th style="padding:12px 16px; font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase; text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clients as $c)
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:16px; font-size:14px; font-weight:600; color:#334155;">
                        <i class="fas fa-building" style="color:#94a3b8; margin-right:8px;"></i> {{ $c->nom_entreprise }}
                    </td>
                    <td style="padding:16px;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <div style="width:60px; height:6px; background:#e2e8f0; border-radius:6px; overflow:hidden;">
                                <div style="width:{{ $c->health }}%; height:100%; background:{{ $c->health < 50 ? '#ef4444' : ($c->health < 80 ? '#f59e0b' : '#10b981') }};"></div>
                            </div>
                            <span style="font-size:12px; font-weight:600; color:#64748b;">{{ $c->health }}%</span>
                        </div>
                    </td>
                    <td style="padding:16px;">
                        @if($c->pending_tasks > 0)
                            <span style="background:#fef3c7; color:#d97706; padding:4px 10px; border-radius:12px; font-size:12px; font-weight:700;">{{ $c->pending_tasks }} tâches</span>
                        @else
                            <span style="color:#94a3b8; font-size:12px;">À jour</span>
                        @endif
                    </td>
                    <td style="padding:16px;">
                        @if($c->pending_docs > 0)
                            <span style="background:#fee2e2; color:#dc2626; padding:4px 10px; border-radius:12px; font-size:12px; font-weight:700;">{{ $c->pending_docs }} docs</span>
                        @else
                            <span style="color:#94a3b8; font-size:12px;">À jour</span>
                        @endif
                    </td>
                    <td style="padding:16px; text-align:right;">
                        <form method="POST" action="{{ route('gel-secretary.switch-client') }}" style="margin:0; display:inline-block;">
                            @csrf
                            <input type="hidden" name="client_id" value="{{ $c->id }}">
                            <button type="submit" style="background:#f1f5f9; color:#0d9488; border:none; padding:6px 12px; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                                Gérer <i class="fas fa-arrow-right"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="padding:40px; text-align:center; color:#94a3b8; font-size:14px;">
                        <i class="fas fa-box-open" style="font-size:32px; margin-bottom:12px; color:#cbd5e1; display:block;"></i>
                        Votre portefeuille est vide.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
