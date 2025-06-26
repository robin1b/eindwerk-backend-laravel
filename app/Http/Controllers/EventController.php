<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

// Zorg dat deze twee imports aanwezig zijn:
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class EventController extends Controller
{
    // Zet hier de trait, dan ziet Intelephense authorize() wél:
    use AuthorizesRequests;

    /**
     * GET /api/events
     * Toon alle publieke events, én private events van de ingelogde user.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = Auth::id();

        $events = Event::query()
            ->where('privacy', 'public')
            ->when($userId, fn($q) => $q->orWhere('organizer_id', $userId))
            ->orderBy('deadline')
            ->get();

        return response()->json($events);
    }

    /**
     * GET /api/user/events
     * Toon louter de events van de ingelogde user.
     */
    public function userEvents(Request $request): JsonResponse
    {
        $events = $request->user()
            ->events()
            ->orderBy('deadline')
            ->get();

        return response()->json($events);
    }

    /**
     * POST /api/events
     * Maak een nieuw event aan voor de ingelogde user.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'admin_name'  => 'required|string|max:255',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline'    => 'required|date',
            'privacy'     => 'required|in:public,private',
            'goal_amount' => 'nullable|numeric|min:0',
        ]);

        // Unieke admin_code
        do {
            $data['admin_code'] = Str::random(12);
        } while (Event::where('admin_code', $data['admin_code'])->exists());

        // Unieke join_code
        do {
            $data['join_code'] = Str::random(8);
        } while (Event::where('join_code', $data['join_code'])->exists());

        // Koppel de organiser
        $data['organizer_id'] = Auth::id();

        // Maak het event
        $event = Event::create($data);

        // Retourneer direct het volledige event met codes
        return response()->json($event, 201);
    }

    /**
     * GET /api/events/{event}
     * Toon een event (ID). Alleen publiek óf van jezelf als private.
     */
    public function show(Event $event): JsonResponse
    {
        if (
            $event->privacy === 'private'
            && $event->organizer_id !== Auth::id()
        ) {
            abort(403);
        }

        return response()->json($event);
    }

    /**
     * PUT /api/events/{event}
     * Werk je eigen event bij.
     */
    public function update(Request $request, Event $event): JsonResponse
    {
        // Policy-check: alleen eigenaar
        $this->authorize('update', $event);

        $data = $request->validate([
            'admin_name'  => 'sometimes|required|string|max:255',
            'name'        => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'deadline'    => 'sometimes|required|date',
            'privacy'     => 'sometimes|required|in:public,private',
            'goal_amount' => 'nullable|numeric|min:0',
        ]);

        $event->update($data);

        return response()->json($event);
    }

    /**
     * DELETE /api/events/{event}
     * Verwijder je eigen event.
     */
    public function destroy(Event $event): Response
    {
        $this->authorize('delete', $event);
        $event->delete();

        return response()->noContent();
    }

    /**
     * GET /api/events/join/{join_code}
     * Publieke gast-view met bijdragen.
     */
    public function showGuest(string $join_code): JsonResponse
    {
        $event = Event::with('contributions')
            ->where('join_code', $join_code)
            ->firstOrFail();

        // Gast mag private events niet zien
        if ($event->privacy === 'private') {
            abort(403);
        }

        return response()->json($event);
    }
}
