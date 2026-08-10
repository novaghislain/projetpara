@php
$user = auth()->user();
$showBanner = false;
$daysLeft = 0;

if ($user && $user->isAutonomousAccountant() && $user->subscription_status === 'trial' && $user->trial_ends_at) {
    $daysLeft = now()->diffInDays($user->trial_ends_at, false);
    if ($daysLeft <= 7 && $daysLeft >= 0) {
        $showBanner = true;
    }
}
@endphp

@if($showBanner)
<div style="background-color: #FFFBEB; color: #D97706; padding: 10px 20px; font-size: 13px; text-align: center; font-weight: 500; border-bottom: 1px solid #FCD34D;">
    <i class="bi bi-exclamation-triangle me-1"></i>
    Attention : il vous reste <strong>{{ ceil($daysLeft) }} jour(s)</strong> de période d'essai. 
    <a href="{{ route('gel-accountant.independant.subscription.index') }}" style="color: #D97706; text-decoration: underline; margin-left: 10px; font-weight: 600;">Choisir une offre</a>
</div>
@endif
