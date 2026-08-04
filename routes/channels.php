<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{cabinet_id}.{client_id}', function ($user, $cabinet_id, $client_id) {
    // Basic auth check: if user is logged in, they can access their own chat
    // You could refine this later to strictly check if $user->cabinet_id == $cabinet_id
    // or if $user->client_id == $client_id
    return auth()->check();
});
