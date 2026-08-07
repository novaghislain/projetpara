@extends('layouts.gel-communication')

@section('title', 'Détail du Brief')

@section('content')
<div class="p-4">
    <div class="mb-4">
        <a href="{{ route('gel-communication.briefs.index') }}" style="color:var(--sec-text-muted); font-size:13px;">
            <i class="fas fa-arrow-left me-1"></i> Retour aux briefs
        </a>
    </div>

    <div class="sec-page-header">
        <div>
            <h1 class="sec-page-title">Brief #{{ $brief->id }} - {{ $brief->client->company_name ?? 'Inconnu' }}</h1>
            <div class="sec-page-sub">Soumis le {{ $brief->created_at->format('d/m/Y à H:i') }}</div>
        </div>
        <div>
            @if($brief->status == 'pending')
                <span class="sec-badge sec-badge-warning" style="font-size:13px; padding:6px 12px;">En attente de traitement</span>
            @elseif($brief->status == 'accepted')
                <span class="sec-badge sec-badge-success" style="font-size:13px; padding:6px 12px;"><i class="fas fa-check me-1"></i> Accepté (Campagne créée)</span>
            @else
                <span class="sec-badge sec-badge-danger" style="font-size:13px; padding:6px 12px;"><i class="fas fa-times me-1"></i> Rejeté</span>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="sec-card">
                <div class="sec-card-header">
                    <h5 class="sec-card-title m-0">Détails de la demande</h5>
                </div>
                <div class="sec-card-body">
                    <div style="background:#F9FAFB; padding:20px; border-radius:8px; border:1px solid var(--sec-border); font-size:14px; line-height:1.6;">
                        <div class="mb-3">
                            <span style="color:var(--sec-text-muted); font-size:12px; font-weight:600; text-transform:uppercase;">Type de Prestation</span><br>
                            <span style="font-size:15px; font-weight:600; color:var(--sec-primary);">{{ ucfirst(str_replace('_', ' ', $brief->type)) }}</span>
                        </div>
                        
                        <div class="mb-3">
                            <span style="color:var(--sec-text-muted); font-size:12px; font-weight:600; text-transform:uppercase;">Description et Objectifs</span><br>
                            <p style="white-space: pre-line; margin-top:5px;">{{ $brief->description }}</p>
                        </div>

                        <div>
                            <span style="color:var(--sec-text-muted); font-size:12px; font-weight:600; text-transform:uppercase;">Budget Estimatif Client</span><br>
                            <span style="font-size:15px; font-weight:700; color:var(--sec-text);">{{ $brief->budget_estimation ? number_format($brief->budget_estimation, 0, ',', ' ') . ' F CFA' : 'Non précisé' }}</span>
                        </div>
                    </div>

                    @if($brief->status == 'pending')
                    <div class="mt-4 pt-4 border-top">
                        <h6 style="font-weight:700; margin-bottom:15px;">Décision</h6>
                        <form action="{{ route('gel-communication.briefs.update_status', $brief->id) }}" method="POST" class="d-flex gap-3">
                            @csrf
                            <input type="hidden" name="status" value="accepted">
                            <button type="submit" class="sec-btn" style="background:#10B981; color:white; padding:10px 20px;">
                                <i class="fas fa-check me-2"></i> Accepter et Créer la Campagne
                            </button>
                        </form>
                        <form action="{{ route('gel-communication.briefs.update_status', $brief->id) }}" method="POST" class="mt-3">
                            @csrf
                            <input type="hidden" name="status" value="rejected">
                            <button type="submit" class="sec-btn sec-btn-secondary" style="color:var(--sec-danger);">
                                <i class="fas fa-times me-2"></i> Rejeter le brief
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="sec-card">
                <div class="sec-card-header">
                    <h5 class="sec-card-title m-0">Informations Client</h5>
                </div>
                <div class="sec-card-body" style="font-size:13px;">
                    @if($brief->client)
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="sec-initials" style="width:40px; height:40px; font-size:14px; background:#e0e7ff; color:#4338ca;">
                                {{ strtoupper(substr($brief->client->company_name, 0, 2)) }}
                            </div>
                            <div>
                                <div style="font-weight:700; font-size:14px;">{{ $brief->client->company_name }}</div>
                                <div style="color:var(--sec-text-muted);">{{ $brief->client->industry ?? 'Secteur non défini' }}</div>
                            </div>
                        </div>
                        <hr>
                        <p><strong>Contact :</strong> {{ $brief->client->phone ?? 'N/A' }}</p>
                        <p><strong>Email :</strong> {{ $brief->client->email ?? 'N/A' }}</p>
                    @else
                        <p class="text-muted">Client introuvable.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
