@extends('layouts.gel-direction')

@section('title', 'Validations — Direction')

@section('page_title', 'Validations & Approbations')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:24px;">
    <div>
        <h1 style="font-size:24px; font-weight:700; color:var(--dir-primary); margin:0;">Validations en Attente</h1>
        <p style="color:var(--dir-text-muted); font-size:14px; margin:4px 0 0 0;">Prenez des décisions sur les congés, recrutements et contrats.</p>
    </div>
</div>

@if(session('success'))
<div style="background:#ECFDF5; border:1px solid #10B981; color:#047857; padding:12px 16px; border-radius:8px; margin-bottom:20px; font-weight:500; font-size:14px;">
    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
</div>
@endif

@if(session('error'))
<div style="background:#FEF2F2; border:1px solid #EF4444; color:#B91C1C; padding:12px 16px; border-radius:8px; margin-bottom:20px; font-weight:500; font-size:14px;">
    <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
</div>
@endif

<div style="background:white; border-radius:12px; border:1px solid var(--dir-border); box-shadow:0 2px 4px rgba(0,0,0,0.02); overflow:hidden;">
    <table style="width:100%; border-collapse:collapse;">
        <thead>
            <tr style="background:#F8FAFC; border-bottom:1px solid var(--dir-border); text-align:left;">
                <th style="padding:16px 20px; font-size:12px; font-weight:600; color:var(--dir-text-muted); text-transform:uppercase;">Type de demande</th>
                <th style="padding:16px 20px; font-size:12px; font-weight:600; color:var(--dir-text-muted); text-transform:uppercase;">Demandeur</th>
                <th style="padding:16px 20px; font-size:12px; font-weight:600; color:var(--dir-text-muted); text-transform:uppercase;">Détails</th>
                <th style="padding:16px 20px; font-size:12px; font-weight:600; color:var(--dir-text-muted); text-transform:uppercase;">Date</th>
                <th style="padding:16px 20px; font-size:12px; font-weight:600; color:var(--dir-text-muted); text-transform:uppercase; text-align:right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($validations as $item)
            <tr style="border-bottom:1px solid #F1F5F9;">
                <td style="padding:16px 20px; font-weight:600; color:var(--dir-text);">{{ $item->type }}</td>
                <td style="padding:16px 20px; color:var(--dir-text-muted);"><i class="fas fa-user-circle me-1"></i> {{ $item->requester }}</td>
                <td style="padding:16px 20px; color:var(--dir-text-muted);">{{ $item->details }}</td>
                <td style="padding:16px 20px; color:var(--dir-text-muted);">{{ $item->date }}</td>
                <td style="padding:16px 20px; text-align:right;">
                    <div style="display:flex; justify-content:flex-end; gap:8px;">
                        <form method="POST" action="{{ route('gel-direction.validations.approve', $item->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm" style="background:#ECFDF5; color:#10B981; border:1px solid #A7F3D0; font-weight:600;">
                                <i class="fas fa-check"></i> Approuver
                            </button>
                        </form>
                        <form method="POST" action="{{ route('gel-direction.validations.reject', $item->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm" style="background:#FEF2F2; color:#EF4444; border:1px solid #FECACA; font-weight:600;">
                                <i class="fas fa-times"></i> Rejeter
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="padding:40px; text-align:center; color:var(--dir-text-muted);">
                    <i class="fas fa-thumbs-up fa-2x mb-3" style="opacity:0.5; color:#10B981;"></i>
                    <p>Toutes les validations ont été traitées.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
