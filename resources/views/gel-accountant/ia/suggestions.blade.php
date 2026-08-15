@extends('layouts.gel-accountant')

@section('title', 'Suggestions IA')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">
            <i class="fas fa-lightbulb" style="color:var(--gel-primary); margin-right:8px;"></i>
            Suggestions & Alertes IA
        </h1>
        <p class="gel-page-subtitle">Recommandations automatiques basées sur l'analyse de vos données comptables.</p>
    </div>
</div>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="gel-card p-4">
            <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 20px;">
                <i class="fas fa-tasks me-2 text-primary"></i> Liste des anomalies détectées
            </h3>
            
            <div class="ia-suggestions-list">
                @forelse($suggestions as $suggestion)
                    <div class="suggestion-item p-4 mb-3" style="border:1px solid var(--gel-border); border-radius:12px; border-left:5px solid {{ $suggestion->priority === 'high' ? '#e11d48' : ($suggestion->priority === 'normal' ? '#2563eb' : '#16a34a') }}; background: white; box-shadow: var(--gel-shadow-sm);">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                            <h4 style="font-size:15px; font-weight:600; margin:0; display:flex; align-items:center; gap:8px;">
                                @if($suggestion->type === 'anomaly') 
                                    <i class="fas fa-exclamation-triangle text-danger"></i> 
                                @elseif($suggestion->type === 'optimization') 
                                    <i class="fas fa-chart-line text-primary"></i>
                                @else 
                                    <i class="fas fa-info-circle text-success"></i> 
                                @endif
                                {{ $suggestion->title }}
                            </h4>
                            <span style="font-size:12px; color:var(--gel-text-secondary); background: #f1f5f9; padding: 4px 8px; border-radius: 12px; font-weight: 500;">
                                {{ \Carbon\Carbon::parse($suggestion->created_at)->diffForHumans() }}
                            </span>
                        </div>
                        
                        <p style="font-size:14px; color:var(--gel-text-secondary); margin-bottom: 16px; line-height: 1.5;">
                            {{ $suggestion->message }}
                        </p>

                        <div style="display: flex; gap: 10px; border-top: 1px dashed var(--gel-border); padding-top: 12px;">
                            <button class="gel-btn gel-btn-primary gel-btn-sm" onclick="showToast('Action appliquée avec succès', 'success')">
                                <i class="fas fa-check"></i> Appliquer
                            </button>
                            <button class="gel-btn gel-btn-secondary gel-btn-sm" onclick="this.closest('.suggestion-item').style.display='none'">
                                <i class="fas fa-times"></i> Ignorer
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5" style="color:var(--gel-text-secondary);">
                        <div style="width:80px; height:80px; background:#f0fdf4; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 16px; font-size:32px; color:#16a34a;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h4 style="font-weight: 600; color: var(--gel-text-primary); margin-bottom: 8px;">Tout est en ordre</h4>
                        <p>L'IA n'a détecté aucune anomalie ou suggestion pour le moment.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection