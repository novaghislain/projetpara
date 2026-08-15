@extends('layouts.gel-client')

@section('title', 'Coordination avec le Cabinet')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Messagerie Cabinet</h1>
            <p class="text-muted mt-1">Discutez directement avec votre expert-comptable ou le secrétariat.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body p-0">
                    <div class="p-4" style="height: 400px; overflow-y: auto; background-color: #f8f9fc;">
                        @forelse($messages as $msg)
                            @if($msg->expediteur == 'client')
                                <div class="d-flex justify-content-end mb-3">
                                    <div class="bg-primary text-white p-3 rounded-3" style="max-width: 75%;">
                                        {{ $msg->content }}
                                        <div class="small text-white-50 mt-1 text-end">{{ \Carbon\Carbon::parse($msg->created_at)->format('d/m/Y H:i') }}</div>
                                    </div>
                                </div>
                            @else
                                <div class="d-flex mb-3">
                                    <div class="bg-white border p-3 rounded-3" style="max-width: 75%;">
                                        <div class="small text-muted mb-1"><i class="fas fa-building me-1"></i> Cabinet</div>
                                        {{ $msg->content }}
                                        <div class="small text-muted mt-1">{{ \Carbon\Carbon::parse($msg->created_at)->format('d/m/Y H:i') }}</div>
                                    </div>
                                </div>
                            @endif
                        @empty
                            <div class="text-center text-muted my-5">
                                <i class="fas fa-comments fa-3x mb-3 text-light"></i>
                                <p>Aucun message. Commencez la discussion ci-dessous.</p>
                            </div>
                        @endforelse
                    </div>
                    <div class="p-3 border-top bg-white">
                        <form action="{{ route('gel-client.coordination.send') }}" method="POST">
                            @csrf
                            <div class="input-group">
                                <textarea name="content" class="form-control" placeholder="Écrivez votre message..." rows="2" required></textarea>
                                <button class="btn btn-primary px-4" type="submit">Envoyer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
