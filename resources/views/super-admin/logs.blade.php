@extends('layouts.gel-super-admin')

@section('title', 'Audit Logs')

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h1 style="font-size: 24px; font-weight: 700; color: #111827;">Journal d'Audit (Logs)</h1>
        <div style="color: #6B7280; font-size: 14px;">Traces des actions système et de sécurité</div>
    </div>
    <button class="btn btn-light border">
        <i class="fas fa-download"></i> Exporter
    </button>
</div>

<div class="sa-card">
    <div class="sa-card-body p-0">
        <table class="table mb-0" style="font-size: 14px;">
            <thead style="background: #F9FAFB;">
                <tr>
                    <th class="border-0 px-4 py-3 text-muted">Date & Heure</th>
                    <th class="border-0 px-4 py-3 text-muted">Utilisateur</th>
                    <th class="border-0 px-4 py-3 text-muted">Action / Événement</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                <tr>
                    <td class="px-4 py-3 text-muted" style="font-family: monospace;">
                        {{ \Carbon\Carbon::parse($log['date'])->format('d/m/Y H:i:s') }}
                    </td>
                    <td class="px-4 py-3 font-weight-bold">{{ $log['user'] }}</td>
                    <td class="px-4 py-3">{{ $log['action'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
