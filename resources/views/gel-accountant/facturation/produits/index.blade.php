@extends('layouts.gel-accountant')

@section('title', 'Catalogue de Produits & Services')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-slate-800 dark:text-slate-100 font-bold">Catalogue ✨</h1>
            <p class="text-sm text-slate-500">Gérez vos produits et services pour la facturation.</p>
        </div>
        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
            <a href="{{ route('gel-accountant.facturation.produits.create') }}" class="btn bg-indigo-500 hover:bg-indigo-600 text-white">
                <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                    <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                </svg>
                <span class="hidden xs:block ml-2">Ajouter un produit</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 rounded-sm text-sm bg-emerald-100 border border-emerald-200 text-emerald-600">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white dark:bg-slate-800 shadow-lg rounded-sm border border-slate-200 dark:border-slate-700">
        <div class="p-3">
            <div class="overflow-x-auto">
                <table class="table-auto w-full dark:text-slate-300">
                    <thead class="text-xs uppercase text-slate-400 dark:text-slate-500 bg-slate-50 dark:bg-slate-700 dark:bg-opacity-50 rounded-sm">
                        <tr>
                            <th class="p-2"><div class="font-semibold text-left">Nom</div></th>
                            <th class="p-2"><div class="font-semibold text-left">Type</div></th>
                            <th class="p-2"><div class="font-semibold text-left">Prix unitaire</div></th>
                            <th class="p-2"><div class="font-semibold text-left">Compte Comptable</div></th>
                            <th class="p-2"><div class="font-semibold text-right">Actions</div></th>
                        </tr>
                    </thead>
                    <tbody class="text-sm font-medium divide-y divide-slate-100 dark:divide-slate-700">
                        @forelse($produits as $produit)
                        <tr>
                            <td class="p-2">
                                <div class="text-slate-800 dark:text-slate-100">{{ $produit->nom }}</div>
                                <div class="text-xs text-slate-500">{{ Str::limit($produit->description, 50) }}</div>
                            </td>
                            <td class="p-2">
                                @if($produit->est_service)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">Service</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800">Bien/Produit</span>
                                @endif
                            </td>
                            <td class="p-2">
                                <div class="text-slate-800 dark:text-slate-100">{{ number_format($produit->prix_unitaire, 0, ',', ' ') }} FCFA</div>
                            </td>
                            <td class="p-2">
                                <div class="text-slate-500">
                                    {{ $produit->compteComptable ? $produit->compteComptable->numero . ' - ' . $produit->compteComptable->intitule : 'Aucun' }}
                                </div>
                            </td>
                            <td class="p-2 text-right">
                                <a href="{{ route('gel-accountant.facturation.produits.edit', $produit->id) }}" class="text-indigo-500 hover:text-indigo-600 mr-2">Modifier</a>
                                <form action="{{ route('gel-accountant.facturation.produits.destroy', $produit->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-500 hover:text-rose-600" onclick="return confirm('Supprimer ce produit ?');">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="p-4 text-center text-slate-500">Aucun produit dans le catalogue.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="p-3 border-t border-slate-200 dark:border-slate-700">
            {{ $produits->links() }}
        </div>
    </div>
</div>
@endsection
