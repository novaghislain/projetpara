@extends('layouts.gel-secretary')
@section('title', 'Collaborateurs du Cabinet')

@section('content')
<div style="padding:20px 0;">
    <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:24px; border-bottom:1px solid #e2e8f0; padding-bottom:12px;">
        <div>
            <h1 style="font-size:24px; font-weight:800; color:#1e293b; margin:0;">Équipe & Collaborateurs</h1>
            <p style="color:#64748b; margin:4px 0 0 0;">Gérez vos assistants et leur accès aux clients.</p>
        </div>
        <div>
            <button onclick="alert('Module d\'invitation en construction.')" style="background:#0d9488; color:white; padding:10px 16px; border:none; border-radius:8px; font-weight:600; cursor:pointer;">
                <i class="fas fa-user-plus"></i> Inviter un collaborateur
            </button>
        </div>
    </div>

    <div style="background:white; border-radius:12px; border:1px solid #e2e8f0; overflow:hidden;">
        <table style="width:100%; border-collapse:collapse; text-align:left;">
            <thead>
                <tr style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                    <th style="padding:12px 16px; font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;">Nom</th>
                    <th style="padding:12px 16px; font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;">Email</th>
                    <th style="padding:12px 16px; font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;">Clients affectés</th>
                    <th style="padding:12px 16px; font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase; text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($collaborateurs as $c)
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:16px; font-size:14px; font-weight:600; color:#334155;">
                        <div style="display:flex; align-items:center; gap:12px;">
                            <div style="width:32px; height:32px; background:#e2e8f0; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#64748b; font-size:12px; font-weight:700;">
                                {{ substr($c->name, 0, 1) }}
                            </div>
                            {{ $c->name }}
                        </div>
                    </td>
                    <td style="padding:16px; color:#64748b; font-size:14px;">{{ $c->email }}</td>
                    <td style="padding:16px;">
                        <span style="background:#f1f5f9; color:#475569; padding:4px 10px; border-radius:12px; font-size:12px; font-weight:700;">{{ $c->clients }} clients</span>
                    </td>
                    <td style="padding:16px; text-align:right;">
                        <button style="background:transparent; border:1px solid #cbd5e1; color:#475569; padding:6px 12px; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer;" onclick="alert('Affectations en construction.')">
                            <i class="fas fa-cog"></i> Gérer
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="padding:40px; text-align:center; color:#94a3b8; font-size:14px;">
                        <i class="fas fa-users" style="font-size:32px; margin-bottom:12px; color:#cbd5e1; display:block;"></i>
                        Vous n'avez pas encore de collaborateurs.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
