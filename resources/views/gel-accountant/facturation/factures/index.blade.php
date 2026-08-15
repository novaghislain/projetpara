@extends('layouts.gel-accountant')

@section('title', 'Factures Clients')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-slate-800 dark:text-slate-100 font-bold">Factures Clients ✨</h1>
            <p class="text-sm text-slate-500">Gérez vos factures et leur intégration comptable.</p>
        </div>
        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
            <a href="{{ route('gel-accountant.facturation.factures.create') }}" class="btn bg-indigo-500 hover:bg-indigo-600 text-white">
                <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                    <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                </svg>
                <span class="hidden xs:block ml-2">Créer une facture</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 rounded-sm text-sm bg-emerald-100 border border-emerald-200 text-emerald-600">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 px-4 py-3 rounded-sm text-sm bg-rose-100 border border-rose-200 text-rose-600">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white dark:bg-slate-800 shadow-lg rounded-sm border border-slate-200 dark:border-slate-700">
        <div class="p-3">
            <div class="overflow-x-auto">
                <table class="table-auto w-full dark:text-slate-300">
                    <thead class="text-xs uppercase text-slate-400 dark:text-slate-500 bg-slate-50 dark:bg-slate-700 dark:bg-opacity-50 rounded-sm">
                        <tr>
                            <th class="p-2"><div class="font-semibold text-left">Numéro</div></th>
                            <th class="p-2"><div class="font-semibold text-left">Date</div></th>
                            <th class="p-2"><div class="font-semibold text-left">Client</div></th>
                            <th class="p-2"><div class="font-semibold text-left">Montant TTC</div></th>
                            <th class="p-2"><div class="font-semibold text-center">Statut</div></th>
                            <th class="p-2"><div class="font-semibold text-right">Actions</div></th>
                        </tr>
                    </thead>
                    <tbody class="text-sm font-medium divide-y divide-slate-100 dark:divide-slate-700">
                        @forelse($factures as $facture)
                        <tr>
                            <td class="p-2">
                                <a href="{{ route('gel-accountant.facturation.factures.show', $facture->id) }}" class="text-indigo-500 hover:text-indigo-600 font-medium">
                                    {{ $facture->numero }}
                                </a>
                            </td>
                            <td class="p-2">
                                <div class="text-slate-800 dark:text-slate-100">{{ \Carbon\Carbon::parse($facture->date_facture)->format('d/m/Y') }}</div>
                            </td>
                            <td class="p-2">
                                <div class="text-slate-800 dark:text-slate-100">{{ $facture->contact->company ?? ($facture->contact->first_name . ' ' . $facture->contact->last_name) }}</div>
                            </td>
                            <td class="p-2">
                                <div class="text-slate-800 dark:text-slate-100">{{ number_format($facture->montant_ttc, 0, ',', ' ') }} FCFA</div>
                            </td>
                            <td class="p-2 text-center">
                                @if($facture->statut === 'brouillon')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">Brouillon</span>
                                @elseif($facture->statut === 'validee')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800">Validée</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-800">{{ ucfirst($facture->statut) }}</span>
                                @endif
                            </td>
                            <td class="p-2 text-right">
                                <a href="{{ route('gel-accountant.facturation.factures.show', $facture->id) }}" class="text-indigo-500 hover:text-indigo-600 mr-2">Voir</a>
                                
                                @if($facture->statut === 'brouillon')
                                    <form action="{{ route('gel-accountant.facturation.factures.destroy', $facture->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-500 hover:text-rose-600" onclick="return confirm('Supprimer cette facture ?');">Supprimer</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="p-4 text-center text-slate-500">Aucune facture trouvée.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="p-3 border-t border-slate-200 dark:border-slate-700">
            {{ $factures->links() }}
        </div>
    </div>
</div>
@endsection
