@extends('layouts.gel-accountant')

@section('title', 'Notes de Frais')

@section('content')

{{-- ═══════════ EN-TÊTE ═══════════ --}}
<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-receipt" style="color:var(--gel-primary); margin-right:8px;"></i> Notes de Frais</h1>
        <p class="gel-page-subtitle">Validez et suivez les notes de frais des employés</p>
    </div>
    <div style="display:flex; gap:8px;">
        <a href="{{ route('gel-accountant.expense-claims.create') }}" class="gel-btn gel-btn-primary">
            <i class="fas fa-plus"></i> Soumettre une note
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success mt-3 mb-3">{{ session('success') }}</div>
@endif

{{-- ═══════════ TABLEAU ═══════════ --}}
<div class="gel-card gel-p-0 p-4 mb-4" style="overflow:hidden;">
    @if($claims->count() > 0)
    <table class="gel-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Employé</th>
                <th>Catégorie</th>
                <th>Description</th>
                <th style="text-align:right;">Montant</th>
                <th>Statut</th>
                <th style="text-align:center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($claims as $claim)
            <tr>
                <td>{{ $claim->created_at->format('d/m/Y') }}</td>
                <td style="font-weight:600;">{{ $claim->employee ? $claim->employee->first_name.' '.$claim->employee->last_name : '-' }}</td>
                <td>{{ ucfirst($claim->category) }}</td>
                <td>{{ Str::limit($claim->description, 30) }}</td>
                <td style="text-align:right; font-weight:600;">{{ number_format($claim->amount, 0, ',', ' ') }} FCFA</td>
                <td>
                    @if($claim->status == 'pending')
                        <span class="badge bg-warning">En attente</span>
                    @elseif($claim->status == 'approved')
                        <span class="badge bg-success">Approuvée</span>
                    @elseif($claim->status == 'rejected')
                        <span class="badge bg-danger">Rejetée</span>
                    @endif
                </td>
                <td style="text-align:center;">
                    <a href="{{ route('gel-accountant.expense-claims.show', $claim->id) }}" class="gel-btn gel-btn-sm gel-btn-secondary" title="Voir / Valider">
                        <i class="fas fa-eye"></i>
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div style="padding:16px;">
        {{ $claims->links() }}
    </div>
    
    @else
    <div style="padding:60px 20px; text-align:center; color:var(--gel-text-muted);">
        <i class="fas fa-receipt" style="font-size:48px; margin-bottom:16px; opacity:0.5;"></i>
        <h3 style="font-size:18px; font-weight:600; color:var(--gel-text-primary);">Aucune note de frais</h3>
        <p style="margin-bottom:20px;">Aucune note de frais n'a encore été soumise par les employés.</p>
        <a href="{{ route('gel-accountant.expense-claims.create') }}" class="gel-btn gel-btn-primary">
            Soumettre une note de frais
        </a>
    </div>
    @endif
</div>

@endsection
