<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Events\NewChatMessage;

class ChatController extends Controller
{
    /**
     * GET  /api/events/join/{join_code}/chat
     * Retourneert alle berichten voor dit event.
     */
    public function index(string $join_code): JsonResponse
    {
        $event = Event::where('join_code', $join_code)
            ->firstOrFail();

        // Haal de berichten op, inclusief user name
        $messages = $event
            ->chatMessages()
            ->with('user:id,name')
            ->orderBy('created_at')
            ->get();

        return response()->json($messages);
    }

    /**
     * POST /api/events/join/{join_code}/chat
     * Slaat een nieuw bericht op (alleen voor ingelogde users).
     */
    public function store(Request $request, string $join_code): JsonResponse
    {
        // 1) Zorg dat er een ingelogde user is
        $user = $request->user();
        if (! $user) {
            abort(401, 'Niet ingelogd');
        }

        // 2) Valideer input
        $data = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        // 3) Vind het event
        $event = Event::where('join_code', $join_code)
            ->firstOrFail();

        // 4) Maak en sla het nieuwe bericht op
        $msg = $event->chatMessages()->create([
            'user_id' => $user->id,
            'message' => $data['message'],
        ]);

        // 5) Broadcast naar anderen
        broadcast(new NewChatMessage($msg))->toOthers();

        return response()->json($msg, 201);
    }
}
