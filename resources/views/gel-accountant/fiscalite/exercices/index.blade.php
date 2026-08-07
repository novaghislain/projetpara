@extends('layouts.gel-accountant')

@section('title', 'Exercices Fiscaux - GEL Accountant')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-calendar-check" style="color:var(--gel-primary); margin-right:8px;"></i> Exercices Fiscaux</h1>
        <p class="gel-page-subtitle">Gérez et clôturez les exercices comptables de votre client.</p>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success" style="background:#d1fae5; color:#065f46; padding:12px; border-radius:6px; margin-bottom:20px;">
    {{ session('success') }}
</div>
@endif

<div class="gel-card p-0">
    <table class="gel-table">
        <thead>
            <tr>
                <th>Année</th>
                <th>Période</th>
                <th>Statut</th>
                <th>Clôturé le</th>
                <th>Clôturé par</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($years as $year)
                <tr>
                    <td style="font-weight:700; font-size:16px;">{{ $year->year }}</td>
                    <td>{{ $year->date_start?->format('d/m/Y') }} - {{ $year->date_end?->format('d/m/Y') }}</td>
                    <td>
                        @if($year->status === 'open')
                            <span style="background:#dbeafe; color:#1e40af; padding:4px 8px; border-radius:4px; font-size:12px; font-weight:600;"><i class="fas fa-lock-open" style="margin-right:4px;"></i> Ouvert</span>
                        @elseif($year->status === 'closed' || $year->status === 'locked')
                            <span style="background:#fee2e2; color:#b91c1c; padding:4px 8px; border-radius:4px; font-size:12px; font-weight:600;"><i class="fas fa-lock" style="margin-right:4px;"></i> Clôturé</span>
                        @endif
                    </td>
                    <td>{{ $year->closed_at ? $year->closed_at->format('d/m/Y H:i') : '-' }}</td>
                    <td>{{ $year->closedBy ? $year->closedBy->name : '-' }}</td>
                    <td>
                        @if($year->status === 'open')
                            <form action="{{ route('gel-accountant.fiscalite.exercices.close', $year->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Êtes-vous sûr de vouloir clôturer DÉFINITIVEMENT cet exercice ?\n\nCette action va :\n1. Verrouiller toutes les écritures de cette année.\n2. Générer automatiquement les À Nouveaux pour l\'année suivante.\n\nCette action est irréversible.');">
                                @csrf
                                <input type="hidden" name="confirm_close" value="1">
                                <button type="submit" class="gel-btn gel-btn-sm" style="background:#b91c1c; color:white; border:none; padding:6px 12px; border-radius:4px; cursor:pointer;">
                                    <i class="fas fa-power-off"></i> Clôturer l'exercice
                                </button>
                            </form>
                        @else
                            <span style="color:var(--gel-text-secondary); font-style:italic;"><i class="fas fa-check"></i> Terminé</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding:40px; color:var(--gel-text-secondary);">
                        <i class="fas fa-calendar-times" style="font-size:32px; color:#d1d5db; margin-bottom:12px; display:block;"></i>
                        Aucun exercice fiscal trouvé.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
