<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Support\Facades\Gate;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{chat_id}', function ($user, $chat_id) {
    $chat = Chat::find($chat_id);

    if (!$chat) {
        return false;
    }

    return Gate::forUser($user)->allows('getMessages', [Message::class, $chat]);
});
