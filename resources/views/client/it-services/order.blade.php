@extends('layouts.portal')

@section('title', 'Commander du Matériel IT')

@section('content')
<div class="container py-4">
    <div class="mb-4">
        <a href="{{ route('client.it.index', ['slug' => $slug]) }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Retour</a>
    </div>

    <div class="card shadow-sm mx-auto" style="max-width: 600px;">
        <div class="card-header bg-white">
            <h5 class="mb-0">Commander du Matériel Informatique</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Un devis vous sera envoyé pour validation avant le traitement de votre commande.
            </div>

            <form action="{{ route('client.it.store-order', ['slug' => $slug]) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Articles souhaités <span class="text-danger">*</span></label>
                    <textarea name="items" class="form-control" rows="5" placeholder="Listez ici les équipements souhaités :&#10;- 2 Ordinateurs portables Dell (Core i7)&#10;- 1 Imprimante multifonction laser&#10;- ..." required></textarea>
                </div>

                <button type="submit" class="btn btn-success w-100">Transmettre la commande</button>
            </form>
        </div>
    </div>
</div>
@endsection
