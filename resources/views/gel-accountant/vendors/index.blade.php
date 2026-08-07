@extends('layouts.gel-accountant')

@section('title', 'Fournisseurs')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-industry" style="color:var(--gel-primary); margin-right:8px;"></i> Fournisseurs</h1>
        <p class="gel-page-subtitle">Gérez la liste de vos fournisseurs et sous-traitants.</p>
    </div>
    <div style="display:flex; gap:8px;">
        <a href="{{ route('gel-accountant.vendors.create') }}" class="gel-btn gel-btn-primary">
            <i class="fas fa-plus"></i> Nouveau fournisseur
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success mt-3 mb-3">{{ session('success') }}</div>
@endif

<div class="gel-card gel-p-0 p-4 mb-4" style="overflow:hidden;">
    @if($vendors->count() > 0)
    <table class="gel-table">
        <thead>
            <tr>
                <th>Raison sociale / Nom</th>
                <th>Contact</th>
                <th>Téléphone</th>
                <th>Solde dû</th>
                <th style="text-align:center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vendors as $vendor)
            <tr>
                <td style="font-weight:600; color:var(--gel-primary);">{{ $vendor->company_name ?: ($vendor->first_name . ' ' . $vendor->last_name) }}</td>
                <td>{{ $vendor->email ?? '-' }}</td>
                <td>{{ $vendor->phone ?: ($vendor->mobile ?: '-') }}</td>
                <td style="font-weight:600; color:var(--gel-text-primary);">{{ number_format($vendor->balance ?? 0, 0, ',', ' ') }} FCFA</td>
                <td style="text-align:center;">
                    <a href="#" class="gel-btn gel-btn-sm gel-btn-secondary" title="Voir">
                        <i class="fas fa-eye"></i>
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div style="padding:16px;">
        {{ $vendors->links() }}
    </div>
    
    @else
    <div style="padding:60px 20px; text-align:center; color:var(--gel-text-muted);">
        <i class="fas fa-industry" style="font-size:48px; margin-bottom:16px; opacity:0.5;"></i>
        <h3 style="font-size:18px; font-weight:600; color:var(--gel-text-primary);">Aucun fournisseur enregistré</h3>
        <p style="margin-bottom:20px;">Ajoutez vos fournisseurs pour générer des bons de commande et gérer vos dépenses.</p>
        <a href="{{ route('gel-accountant.vendors.create') }}" class="gel-btn gel-btn-primary">
            Nouveau fournisseur
        </a>
    </div>
    @endif
</div>

@endsection
