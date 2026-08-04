@extends('layouts.gel-secretary')
@section('title', 'Centre de Notifications')

@section('content')
<div class="sec-page-header">
  <div>
    <h1 class="sec-page-title">
      <i class="fas fa-bell" style="color:var(--sec-primary); margin-right:8px;"></i>Centre de Notifications
    </h1>
    <p class="sec-page-sub">Alertes, tâches d'escalade et rappels système</p>
  </div>
</div>

<div class="sec-card animate-fade delay-1" style="border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.04);">
  <div class="sec-card-header">
    <div class="sec-card-title"><i class="fas fa-list" style="color:var(--sec-primary);margin-right:6px;"></i> Notifications Récentes</div>
  </div>
  
  <div style="overflow-x:auto;">
    <table class="sec-table">
      <thead>
        <tr style="background:#f9fafb;">
          <th style="padding:14px 18px; width:40px;"></th>
          <th style="padding:14px 18px;">Type / Niveau</th>
          <th style="padding:14px 18px;">Message</th>
          <th style="padding:14px 18px;">Date</th>
          <th style="padding:14px 18px; text-align:right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($notifications as $notif)
          <tr style="border-bottom:1px solid #f3f4f6; {{ is_null($notif->read_at) ? 'background:#F0F9FF;' : '' }}">
            <td style="padding:14px 18px; text-align:center;">
              @if(is_null($notif->read_at))
                <div style="width:10px; height:10px; background:#3B82F6; border-radius:50%; display:inline-block;" title="Non lu"></div>
              @endif
            </td>
            <td style="padding:14px 18px;">
              @php
                  $data = $notif->data;
                  $level = $data['level'] ?? 'info';
                  
                  $bg = '#F3F4F6'; $color = '#4B5563'; $icon = 'fas fa-info-circle';
                  if($level == 'warning') { $bg = '#FFFBEB'; $color = '#D97706'; $icon = 'fas fa-exclamation-triangle'; }
                  if($level == 'danger' || $level == 'error') { $bg = '#FEF2F2'; $color = '#DC2626'; $icon = 'fas fa-times-circle'; }
                  if($level == 'success') { $bg = '#DCFCE7'; $color = '#16A34A'; $icon = 'fas fa-check-circle'; }
              @endphp
              <span style="background:{{$bg}}; color:{{$color}}; padding:4px 8px; border-radius:12px; font-size:12px;">
                <i class="{{$icon}}"></i> {{ ucfirst($level) }}
              </span>
            </td>
            <td style="padding:14px 18px;">
              <div style="font-weight:600; color:#1e293b; margin-bottom:4px;">{{ $data['message'] ?? 'Notification système' }}</div>
              @if(isset($data['action_url']))
                <a href="{{ $data['action_url'] }}" style="font-size:12px; color:#2563eb; text-decoration:none;"><i class="fas fa-external-link-alt"></i> Voir le détail</a>
              @endif
            </td>
            <td style="padding:14px 18px; font-size:12px; color:#64748b;">
              {{ $notif->created_at->diffForHumans() }}
            </td>
            <td style="padding:14px 18px; text-align:right;">
              @if(is_null($notif->read_at))
                <form action="{{ route('gel-secretary.notifications.markAsRead', $notif->id) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="sec-btn" style="background:#f1f5f9; color:#475569; padding:4px 8px; font-size:12px;" title="Marquer comme lu">
                      <i class="fas fa-check"></i>
                    </button>
                </form>
              @endif
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" style="padding:40px; text-align:center; color:#94a3b8; font-weight:600;">
              <i class="fas fa-bell-slash" style="font-size:32px; display:block; margin-bottom:8px; color:#cbd5e1;"></i>
              Vous n'avez aucune notification.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
