@extends('layouts.gel-informaticien')

@section('title', 'Gestion des Commandes d\'Équipement')

@section('content')
<div class="p-4">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
        <div>
            <h1 class="h4 mb-1"><i class="fas fa-boxes text-success"></i> Commandes de Matériel</h1>
            <p class="text-muted mb-0">Suivi et traitement des commandes d'équipements IT des clients.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm border-top-success">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>N° Commande</th>
                            <th>Client</th>
                            <th>Articles demandés</th>
                            <th>Date</th>
                            <th>Statut actuel</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr>
                            <td><strong>{{ $order->order_number }}</strong></td>
                            <td>{{ $order->client->company_name ?? 'Inconnu' }}</td>
                            <td>
                                <ul class="mb-0 ps-3 small">
                                    @foreach($order->items as $item)
                                        <li>{{ Str::limit($item, 40) }}</li>
                                    @endforeach
                                </ul>
                            </td>
                            <td>{{ $order->created_at->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                            </td>
                            <td>
                                <form action="{{ route('gel-informaticien.equipment.update_status', $order->id) }}" method="POST" class="d-flex flex-column gap-2">
                                    @csrf
                                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                        <option value="recue" {{ $order->status == 'recue' ? 'selected' : '' }}>Reçue</option>
                                        <option value="devis_envoye" {{ $order->status == 'devis_envoye' ? 'selected' : '' }}>Devis Envoyé</option>
                                        <option value="valide" {{ $order->status == 'valide' ? 'selected' : '' }}>Validé (Client)</option>
                                        <option value="approvisionnement" {{ $order->status == 'approvisionnement' ? 'selected' : '' }}>En approvisionnement</option>
                                        <option value="expedie" {{ $order->status == 'expedie' ? 'selected' : '' }}>Expédié</option>
                                        <option value="livre" {{ $order->status == 'livre' ? 'selected' : '' }}>Livré</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Aucune commande de matériel à traiter.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($orders->hasPages())
                <div class="p-3 border-top">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
