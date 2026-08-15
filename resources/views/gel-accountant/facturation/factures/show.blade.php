@extends('layouts.gel-accountant')

@section('title', 'Détail Facture ' . $facture->numero)

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-slate-800 dark:text-slate-100 font-bold">Facture {{ $facture->numero }} ✨</h1>
            <div class="mt-1 flex items-center gap-2 text-sm text-slate-500">
                <span>Créée le {{ \Carbon\Carbon::parse($facture->date_facture)->format('d/m/Y') }}</span>
                <span>•</span>
                @if($facture->statut === 'brouillon')
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">Brouillon</span>
                @elseif($facture->statut === 'validee')
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800">Validée</span>
                @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-800">{{ ucfirst($facture->statut) }}</span>
                @endif
            </div>
        </div>
        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
            <a href="{{ route('gel-accountant.facturation.factures.pdf', $facture->id) }}" class="btn bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 text-slate-600 dark:text-slate-300">
                Télécharger PDF
            </a>
            
            @if($facture->statut === 'brouillon')
                <form action="{{ route('gel-accountant.facturation.factures.validate', $facture->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="btn bg-emerald-500 hover:bg-emerald-600 text-white" onclick="return confirm('La validation générera l\'écriture comptable et la facture ne pourra plus être modifiée ni supprimée. Continuer ?');">
                        Valider & Comptabiliser
                    </button>
                </form>
            @endif
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

    <div class="bg-white dark:bg-slate-800 shadow-lg rounded-sm border border-slate-200 dark:border-slate-700 p-6 md:p-10 mb-8 max-w-4xl mx-auto">
        
        <!-- Header Facture -->
        <div class="flex flex-col md:flex-row justify-between mb-8 pb-8 border-b border-slate-200 dark:border-slate-700">
            <div>
                <div class="text-xl font-bold text-slate-800 dark:text-slate-100 mb-2">Mon Cabinet (Cabinet)</div>
                <div class="text-sm text-slate-500">
                    <div>123 Rue de l'Expertise</div>
                    <div>Abidjan, Côte d'Ivoire</div>
                    <div>RCCM: CI-ABJ-2023-B-12345</div>
                </div>
            </div>
            <div class="mt-6 md:mt-0 md:text-right">
                <div class="text-sm text-slate-500 uppercase tracking-wider mb-2">Facturé à</div>
                <div class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-1">
                    {{ $facture->contact->company ?? ($facture->contact->first_name . ' ' . $facture->contact->last_name) }}
                </div>
                <div class="text-sm text-slate-500">
                    @if($facture->contact->address) <div>{{ $facture->contact->address }}</div> @endif
                    @if($facture->contact->email) <div>{{ $facture->contact->email }}</div> @endif
                    @if($facture->contact->phone) <div>{{ $facture->contact->phone }}</div> @endif
                    @if($facture->contact->ifu) <div>IFU: {{ $facture->contact->ifu }}</div> @endif
                </div>
            </div>
        </div>

        <!-- Lignes -->
        <div class="mb-8">
            <table class="w-full">
                <thead class="text-xs uppercase text-slate-400 dark:text-slate-500 bg-slate-50 dark:bg-slate-700 dark:bg-opacity-50">
                    <tr>
                        <th class="p-3 text-left">Description</th>
                        <th class="p-3 text-right">Qté</th>
                        <th class="p-3 text-right">Prix Unitaire (HT)</th>
                        <th class="p-3 text-right">TVA</th>
                        <th class="p-3 text-right">Total HT</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-100 dark:divide-slate-700">
                    @foreach($facture->lignes as $ligne)
                    <tr>
                        <td class="p-3 text-slate-800 dark:text-slate-100">
                            {{ $ligne->description }}
                        </td>
                        <td class="p-3 text-right text-slate-500">{{ $ligne->quantite }}</td>
                        <td class="p-3 text-right text-slate-500">{{ number_format($ligne->prix_unitaire, 0, ',', ' ') }}</td>
                        <td class="p-3 text-right text-slate-500">{{ $ligne->taux_tva }}%</td>
                        <td class="p-3 text-right text-slate-800 dark:text-slate-100 font-medium">{{ number_format($ligne->total_ht, 0, ',', ' ') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totaux -->
        <div class="flex justify-end">
            <div class="w-full max-w-sm">
                <div class="flex justify-between py-2 border-b border-slate-100 dark:border-slate-700">
                    <div class="text-sm text-slate-500">Total HT</div>
                    <div class="text-sm text-slate-800 dark:text-slate-100">{{ number_format($facture->montant_ht, 0, ',', ' ') }} FCFA</div>
                </div>
                <div class="flex justify-between py-2 border-b border-slate-100 dark:border-slate-700">
                    <div class="text-sm text-slate-500">Total TVA</div>
                    <div class="text-sm text-slate-800 dark:text-slate-100">{{ number_format($facture->montant_tva, 0, ',', ' ') }} FCFA</div>
                </div>
                <div class="flex justify-between py-3">
                    <div class="text-lg font-bold text-slate-800 dark:text-slate-100">Net à Payer (TTC)</div>
                    <div class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ number_format($facture->montant_ttc, 0, ',', ' ') }} FCFA</div>
                </div>
            </div>
        </div>

        <!-- Notes -->
        @if($facture->notes)
        <div class="mt-8 pt-8 border-t border-slate-200 dark:border-slate-700">
            <div class="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-2">Notes</div>
            <div class="text-sm text-slate-500">{{ $facture->notes }}</div>
        </div>
        @endif

        <!-- Informations Comptables -->
        @if($facture->ecriture_id)
        <div class="mt-8 pt-4">
            <div class="text-xs text-slate-400">
                <i class="fas fa-check-circle text-emerald-500 mr-1"></i> Intégrée en comptabilité (Écriture #{{ $facture->ecriture_id }})
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
