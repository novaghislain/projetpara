@extends('layouts.gel-accountant')

@section('title', 'Fournisseurs')

@push('styles')
<style>
/* ==========================================================================
   VENDORS - DESIGN
   ========================================================================== */
.table-container {
    background: white; border: 1px solid var(--gel-border);
    border-radius: 12px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03); overflow: hidden;
}
.filters-bar { padding: 16px 20px; border-bottom: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center; background: #F8FAFC; }
.filters-left { display: flex; gap: 12px; align-items: center; }
.form-control-sm { border: 1px solid #E2E8F0; border-radius: 6px; padding: 6px 12px; font-size: 13px; outline: none; }
.form-control-sm:focus { border-color: var(--gel-primary); }

.gel-table { width: 100%; border-collapse: collapse; }
.gel-table th { background: white; padding: 12px 20px; font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; border-bottom: 2px solid #E2E8F0; }
.gel-table td { padding: 12px 20px; border-bottom: 1px solid #F1F5F9; font-size: 13px; color: #1E293B; vertical-align: middle; }
.gel-table tr:hover { background: #F8FAFC; }

.btn-action { background: white; border: 1px solid #E2E8F0; color: #64748B; width: 28px; height: 28px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; text-decoration: none; font-size: 12px; transition: all 0.2s; }
.btn-action:hover { background: #F1F5F9; color: var(--gel-primary); border-color: #CBD5E1; }

.btn-primary-action { background: var(--gel-primary); color: white; border: none; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; transition: all 0.2s; }
.btn-primary-action:hover { background: var(--gel-primary-hover); transform: translateY(-1px); }
</style>
@endpush

@section('content')
<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">Fournisseurs</h1>
        <div class="gel-page-subtitle">Gérez la liste de vos fournisseurs et sous-traitants.</div>
    </div>
    <div style="display:flex; gap:12px;">
        <a href="{{ route('gel-accountant.vendors.create') }}" class="btn-primary-action">
            <i class="fas fa-plus"></i> Nouveau Fournisseur
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success d-flex align-items-center" style="background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; border-radius: 8px; padding:12px 16px; margin-bottom:20px;">
    <i class="fas fa-check-circle me-2" style="font-size:18px;"></i>
    {{ session('success') }}
</div>
@endif

<div class="table-container">
    <div class="filters-bar">
        <div class="filters-left">
            <input type="text" class="form-control-sm" placeholder="Rechercher un fournisseur..." style="width:250px;">
        </div>
    </div>

    <table class="gel-table">
        <thead>
            <tr>
                <th>Entreprise / Nom</th>
                <th>Contact</th>
                <th>Téléphone</th>
                <th>Email</th>
                <th>IFU</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($vendors as $vendor)
            <tr>
                <td style="font-weight:600; color:var(--gel-primary);">{{ $vendor->company_name ?? ($vendor->first_name . ' ' . $vendor->last_name) }}</td>
                <td>{{ $vendor->first_name }} {{ $vendor->last_name }}</td>
                <td>{{ $vendor->phone }}</td>
                <td>{{ $vendor->email }}</td>
                <td>{{ $vendor->tax_id }}</td>
                <td class="text-end">
                    <!-- Action Buttons -->
                    <a href="#" class="btn-action" title="Détails"><i class="fas fa-eye"></i></a>
                    <a href="#" class="btn-action" title="Modifier"><i class="fas fa-edit"></i></a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6">
                    <div class="text-center" style="padding: 40px 20px; color: #94A3B8;">
                        <i class="fas fa-industry" style="font-size: 32px; margin-bottom: 12px; opacity:0.5;"></i>
                        <div>Aucun fournisseur enregistré.</div>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    @if($vendors->hasPages())
    <div style="padding: 16px 20px; border-top: 1px solid #E2E8F0; display:flex; justify-content:flex-end;">
        {{ $vendors->links() }}
    </div>
    @endif
</div>
@endsection
