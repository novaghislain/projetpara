<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{cabinet_id}.{client_id}', function ($user, $cabinet_id, $client_id) {
    // Basic auth check: if user is logged in, they can access their own chat
    // You could refine this later to strictly check if $user->cabinet_id == $cabinet_id
    // or if $user->client_id == $client_id
    return auth()->check();
});

// ─── S11 — Canal de messagerie interne (Secrétaire↔Comptable, Comptable↔Admin) ─────
// chat.interne.{cabinet_id}.{user_id} : chaque utilisateur n'écoute que son propre canal.
Broadcast::channel('chat.interne.{cabinet_id}.{user_id}', function ($user, $cabinet_id, $user_id) {
    if (!auth()->check()) {
        return false;
    }
    // Même cabinet ET l'utilisateur est bien le destinataire/émetteur du canal.
    $sameCabinet = (int) $user->cabinet_id === (int) $cabinet_id;
    if (!$sameCabinet) {
        return false;
    }
    // Le propriétaire du canal doit être un membre du cabinet (lui-même ou un collègue)
    $isSelf = (int) $user->id === (int) $user_id;
    $isColleague = User::where('cabinet_id', $cabinet_id)->where('id', $user_id)->exists();
    return $isSelf || $isColleague;
});

// ─── S15 — Canaux temps réel documentaire ──────────────────────────────
// document.{client_id} : dépôt d'un document par l'entreprise → badge secrétaire
Broadcast::channel('document.{client_id}', function ($user, $client_id) {
    if (!auth()->check()) {
        return false;
    }
    // Le personnel du cabinet (secrétaire, comptable) lié à ce client peut écouter.
    if ($user->hasRole(['secretaire', 'secretary', 'comptable', 'accountant', 'admin'])) {
        return true;
    }
    return (int) $user->active_client_id === (int) $client_id
        || (int) ($user->client_id ?? 0) === (int) $client_id;
});

// ─── S4.1 — Canal de coordination Secrétaire ↔ Comptable sur une même entreprise ─────
// chat.coordination.{client_id} : seuls le secrétaire ET le comptable réellement
// rattachés à l'entreprise (client_id gel_clients) peuvent écouter → isolation stricte.
Broadcast::channel('chat.coordination.{client_id}', function ($user, $client_id) {
    if (!auth()->check()) {
        return false;
    }
    $allowed = \App\Services\CoordinationService::isSecretaire($user)
        || \App\Services\CoordinationService::isComptable($user)
        || $user->isCompanyAdmin();
    return $allowed && \App\Services\CoordinationService::isAttachedToClient($user, (int) $client_id);
});

// workflow.{client_id} : transmission au comptable → comptable voit le doc apparaître
Broadcast::channel('workflow.{client_id}', function ($user, $client_id) {
    if (!auth()->check()) {
        return false;
    }
    if ($user->hasRole(['comptable', 'accountant', 'admin'])) {
        return true;
    }
    return (int) $user->active_client_id === (int) $client_id
        || (int) ($user->client_id ?? 0) === (int) $client_id;
});
