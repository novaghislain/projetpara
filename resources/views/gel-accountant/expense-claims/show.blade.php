@extends('layouts.gel-accountant')

@section('title', 'Détail Note de Frais')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-receipt" style="color:var(--gel-primary); margin-right:8px;"></i> Note de frais #{{ $claim->id }}</h1>
        <p class="gel-page-subtitle">Soumise par {{ $claim->employee->first_name }} {{ $claim->employee->last_name }} le {{ $claim->created_at->format('d/m/Y') }}</p>
    </div>
    <div style="display:flex; gap:8px;">
        <a href="{{ route('gel-accountant.expense-claims.index') }}" class="gel-btn gel-btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success mt-3 mb-3">{{ session('success') }}</div>
@endif

<div class="row">
    <div class="col-md-7">
        <div class="gel-card p-4 mb-4">
            <h3 style="font-size:15px; font-weight:700; margin-bottom:20px; border-bottom:1px solid #eee; padding-bottom:10px;">Informations</h3>
            
            <div class="row mb-3">
                <div class="col-sm-4 text-muted">Employé :</div>
                <div class="col-sm-8 font-weight-bold">{{ $claim->employee->first_name }} {{ $claim->employee->last_name }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-4 text-muted">Poste / Dépt :</div>
                <div class="col-sm-8">{{ $claim->employee->position }} - {{ $claim->employee->department }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-4 text-muted">Catégorie :</div>
                <div class="col-sm-8">{{ ucfirst($claim->category) }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-4 text-muted">Montant :</div>
                <div class="col-sm-8" style="font-weight:700; color:var(--gel-primary); font-size:16px;">{{ number_format($claim->amount, 0, ',', ' ') }} FCFA</div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-4 text-muted">Description :</div>
                <div class="col-sm-8">{{ $claim->description }}</div>
            </div>
        </div>

        <div class="gel-card p-4 mb-4">
            <h3 style="font-size:15px; font-weight:700; margin-bottom:20px; border-bottom:1px solid #eee; padding-bottom:10px;">Validation</h3>
            
            <div class="mb-4">
                <strong>Statut actuel :</strong>
                @if($claim->status == 'pending')
                    <span class="badge bg-warning ms-2">En attente d'approbation</span>
                @elseif($claim->status == 'approved')
                    <span class="badge bg-success ms-2">Approuvée</span>
                @elseif($claim->status == 'rejected')
                    <span class="badge bg-danger ms-2">Rejetée</span>
                @endif
            </div>

            @if($claim->status == 'pending')
            <div class="d-flex gap-3 mt-4">
                <form action="{{ route('gel-accountant.expense-claims.update-status', $claim->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="status" value="approved">
                    <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Approuver la dépense</button>
                </form>
                <form action="{{ route('gel-accountant.expense-claims.update-status', $claim->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="status" value="rejected">
                    <button type="submit" class="btn btn-outline-danger"><i class="fas fa-times"></i> Rejeter</button>
                </form>
            </div>
            <div class="mt-3 text-muted" style="font-size:12px;">
                <i class="fas fa-info-circle"></i> Approuver cette note de frais autorisera son remboursement ou sa comptabilisation.
            </div>
            @endif
        </div>
    </div>

    <div class="col-md-5">
        <div class="gel-card p-4 mb-4">
            <h3 style="font-size:15px; font-weight:700; margin-bottom:20px; border-bottom:1px solid #eee; padding-bottom:10px;"><i class="fas fa-paperclip"></i> Justificatif</h3>
            @if($claim->receipt_path)
                @php
                    $ext = strtolower(pathinfo($claim->receipt_path, PATHINFO_EXTENSION));
                @endphp
                
                @if(in_array($ext, ['jpg', 'jpeg', 'png']))
                    <div style="text-align:center; margin-bottom:15px;">
                        <img src="{{ Storage::url($claim->receipt_path) }}" alt="Reçu" style="max-width:100%; border-radius:8px; border:1px solid #ddd;">
                    </div>
                @else
                    <div style="text-align:center; padding:30px; background:#f8f9fa; border-radius:8px; margin-bottom:15px;">
                        <i class="fas fa-file-pdf" style="font-size:48px; color:#e25555; margin-bottom:10px;"></i>
                        <div>Document PDF</div>
                    </div>
                @endif
                <div class="d-grid">
                    <a href="{{ Storage::url($claim->receipt_path) }}" target="_blank" class="btn btn-outline-primary"><i class="fas fa-external-link-alt"></i> Ouvrir le document</a>
                </div>
            @else
                <div style="text-align:center; padding:30px; color:#999;">
                    <i class="fas fa-file-image" style="font-size:48px; margin-bottom:10px; opacity:0.3;"></i>
                    <p class="mb-0">Aucun justificatif joint</p>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
