@extends('layouts.gel-secretary')
@section('title', 'Tâches Consolidées')

@section('content')
<div style="padding:20px 0;">
    <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:24px; border-bottom:1px solid #e2e8f0; padding-bottom:12px;">
        <div>
            <h1 style="font-size:24px; font-weight:800; color:#1e293b; margin:0;">Tâches de tout le portefeuille</h1>
            <p style="color:#64748b; margin:4px 0 0 0;">Visualisez toutes les échéances de l'ensemble de vos clients au même endroit.</p>
        </div>
    </div>

    <div style="background:white; border-radius:12px; border:1px solid #e2e8f0; overflow:hidden;">
        <table style="width:100%; border-collapse:collapse; text-align:left;">
            <thead>
                <tr style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                    <th style="padding:12px 16px; font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;">Entreprise</th>
                    <th style="padding:12px 16px; font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;">Tâche</th>
                    <th style="padding:12px 16px; font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;">Statut</th>
                    <th style="padding:12px 16px; font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;">Échéance</th>
                </tr>
            </thead>
            <tbody>
                @forelse($taches as $t)
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:16px; font-size:14px; font-weight:600; color:#334155;">
                        <i class="fas fa-building" style="color:#94a3b8; margin-right:6px;"></i> {{ $t->client->nom_entreprise ?? 'Inconnu' }}
                    </td>
                    <td style="padding:16px; color:#475569; font-size:14px;">
                        <div style="font-weight:600;">{{ $t->titre }}</div>
                    </td>
                    <td style="padding:16px;">
                        @if($t->statut == 'termine')
                            <span style="background:#dcfce7; color:#166534; padding:4px 10px; border-radius:12px; font-size:12px; font-weight:700;">Terminé</span>
                        @elseif($t->statut == 'en_cours')
                            <span style="background:#dbeafe; color:#1e40af; padding:4px 10px; border-radius:12px; font-size:12px; font-weight:700;">En cours</span>
                        @else
                            <span style="background:#f1f5f9; color:#475569; padding:4px 10px; border-radius:12px; font-size:12px; font-weight:700;">À faire</span>
                        @endif
                    </td>
                    <td style="padding:16px; font-size:13px; color:#64748b;">
                        @if(\Carbon\Carbon::parse($t->date_echeance)->isPast() && $t->statut != 'termine')
                            <span style="color:#dc2626; font-weight:700;"><i class="fas fa-exclamation-circle"></i> {{ \Carbon\Carbon::parse($t->date_echeance)->format('d/m/Y') }}</span>
                        @else
                            {{ \Carbon\Carbon::parse($t->date_echeance)->format('d/m/Y') }}
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="padding:40px; text-align:center; color:#94a3b8; font-size:14px;">
                        <i class="fas fa-tasks" style="font-size:32px; margin-bottom:12px; color:#cbd5e1; display:block;"></i>
                        Aucune tâche en cours pour le moment.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
