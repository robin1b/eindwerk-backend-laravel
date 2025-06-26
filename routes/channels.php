<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat.{eventId}', function ($user, $eventId) {
    // alleen organisatoren of ingelogde users mogen luisteren
    // wij laten iedereen luisteren, dus return true
    return true;
});
