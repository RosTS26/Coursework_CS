<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Auth;

Broadcast::channel('Friendly-chat-{id}', function($user, $id) {
    return (int) $user->id === (int) $id;
});

// Online/offline check
Broadcast::channel('Auth-check', function($user) {
    if (auth()->check()) {
        return ['id' => $user->id, 'name' => $user->name];
    }
});
