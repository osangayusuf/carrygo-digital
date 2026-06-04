<?php

use App\Models\Ticket;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('support.ticket.{id}', function ($user, $id) {
    if ($user->isAdmin()) {
        return true;
    }

    if (! $user->isApprovedAgent()) {
        return false;
    }

    $ticket = Ticket::find($id);
    if (! $ticket) {
        return false;
    }

    return (int) $ticket->agent_id === (int) $user->id || $ticket->isUnassigned();
});

Broadcast::channel('support.agents', function ($user) {
    if ($user->isAdmin() || $user->isApprovedAgent()) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ];
    }

    return false;
});
