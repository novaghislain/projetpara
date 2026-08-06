@extends('layouts.app')
@section('title', 'Demande de contact — ' . $client->nom_entreprise)

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4 p-md-5">
                    <h2 class="fw-bold mb-3 text-center" style="color: var(--sec-primary, #163A5E);">Contacter {{ $client->nom_entreprise }}</h2>
                    <p class="text-muted text-center mb-4">Remplissez le formulaire ci-dessous pour nous envoyer votre demande. Notre équipe vous répondra dans les plus brefs délais.</p>

                    @if(session('success'))
                        <div class="alert alert-success fw-bold rounded-3">
                            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('public.contact.submit', $client->slug) }}" method="POST">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Nom complet <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control bg-light" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Adresse e-mail <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control bg-light" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Téléphone</label>
                                <input type="text" name="phone" class="form-control bg-light">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Type de demande <span class="text-danger">*</span></label>
                                <select name="type" class="form-select bg-light" required>
                                    <option value="general">Demande d'information générale</option>
                                    <option value="quote">Demande de devis</option>
                                    <option value="support">Support technique / Assistance</option>
                                    <option value="other">Autre demande</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Sujet <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control bg-light" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Votre message <span class="text-danger">*</span></label>
                            <textarea name="description" class="form-control bg-light" rows="5" required></textarea>
                        </div>

                        <button type="submit" class="btn w-100 py-2 fw-bold" style="background: var(--sec-primary, #163A5E); color: white;">
                            <i class="fas fa-paper-plane me-2"></i> Envoyer la demande
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
