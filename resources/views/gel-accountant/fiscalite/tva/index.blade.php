@extends('layouts.gel-accountant')

@section('title', 'Déclarations de TVA - GEL Accountant')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-file-invoice-dollar" style="color:var(--gel-primary); margin-right:8px;"></i> Déclarations de TVA</h1>
        <p class="gel-page-subtitle">Gérez et soumettez les déclarations de TVA de votre client.</p>
    </div>
    <div style="display:flex; gap:12px;">
        <a href="{{ route('gel-accountant.fiscalite.tva.create') }}" class="gel-btn gel-btn-primary">
            <i class="fas fa-plus"></i> Nouvelle Déclaration
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success" style="background:#d1fae5; color:#065f46; padding:12px; border-radius:6px; margin-bottom:20px;">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert alert-danger" style="background:#fee2e2; color:#b91c1c; padding:12px; border-radius:6px; margin-bottom:20px;">
    {{ session('error') }}
</div>
@endif

<div class="gel-card p-0">
    <table class="gel-table">
        <thead>
            <tr>
                <th>Période</th>
                <th>Type</th>
                <th>TVA Collectée</th>
                <th>TVA Déductible</th>
                <th>TVA Nette à payer</th>
                <th>Statut</th>
                <th>Créée par</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($declarations as $dec)
                <tr>
                    <td style="font-weight:600;">{{ $dec->period }}</td>
                    <td>{{ ucfirst($dec->type) }}</td>
                    <td>{{ number_format($dec->tva_collected, 2, ',', ' ') }}</td>
                    <td>{{ number_format($dec->tva_deductible, 2, ',', ' ') }}</td>
                    <td style="font-weight:700; color:var(--gel-primary);">{{ number_format($dec->tva_net, 2, ',', ' ') }}</td>
                    <td>
                        @if($dec->status === 'draft')
                            <span style="background:#f3f4f6; padding:4px 8px; border-radius:4px; font-size:12px; font-weight:600;">Brouillon</span>
                        @elseif($dec->status === 'submitted')
                            <span style="background:#dbeafe; color:#1e40af; padding:4px 8px; border-radius:4px; font-size:12px; font-weight:600;">Soumise</span>
                        @elseif($dec->status === 'approved')
                            <span style="background:#d1fae5; color:#065f46; padding:4px 8px; border-radius:4px; font-size:12px; font-weight:600;">Approuvée</span>
                        @elseif($dec->status === 'paid')
                            <span style="background:#d1fae5; color:#065f46; padding:4px 8px; border-radius:4px; font-size:12px; font-weight:600;">Payée</span>
                        @endif
                    </td>
                    <td>{{ $dec->createdBy ? $dec->createdBy->name : '-' }}</td>
                    <td>
                        @if($dec->status === 'draft')
                            <form action="{{ route('gel-accountant.fiscalite.tva.submit', $dec->id) }}" method="POST" style="display:inline-block;">
                                @csrf
                                <button type="submit" class="gel-btn gel-btn-sm" style="background:#1e40af; color:white;" onclick="return confirm('Soumettre cette déclaration ?');">
                                    <i class="fas fa-check"></i> Soumettre
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding:40px; color:var(--gel-text-secondary);">
                        <i class="fas fa-file-invoice-dollar" style="font-size:32px; color:#d1d5db; margin-bottom:12px; display:block;"></i>
                        Aucune déclaration de TVA trouvée.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
