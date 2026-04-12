<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\ChatPerm;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ChatController extends Controller
{
    public function index(Team $team)
    {
        Gate::authorize('viewAny', [Chat::class, $team]);

        $role = $team->users()
            ->wherePivot('user_id', Auth::id())
            ->first()?->pivot?->role;

        $messagesQuery = fn ($query) => $query
            ->orderBy('created_at', 'desc')
            ->limit(30)
            ->with('user:id,name');

        if (in_array($role, ['admin', 'owner'], true)) {
            $chats = Chat::where('team_id', $team->id)
                ->with(['messages' => $messagesQuery])
                ->get();

            return Inertia::render('chat', ['data' => $chats]);
        }

        if ($role === 'member') {
            $chats = Chat::where('team_id', $team->id)
                ->where(function ($query) {
                    $query->whereRelation('perm', 'visibility', 'viewer')
                        ->orWhereRelation('perm', 'visibility', 'member');
                })
                ->with(['messages' => $messagesQuery])
                ->get();

            return Inertia::render('chat', ['data' => $chats]);
        }

        if ($role === 'viewer') {
            $chats = Chat::where('team_id', $team->id)
                ->whereRelation('perm', 'visibility', 'viewer')
                ->with(['messages' => $messagesQuery])
                ->get();

            return Inertia::render('chat', ['data' => $chats]);
        }

        return back()->with(['error' => 'cannot retrieve chats']);
    }

    public function store(Request $request, Team $team)
    {
        Gate::authorize('create', [Chat::class, $team]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:text,voice',
        ]);

        $chat = Chat::create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'team_id' => $team->id,
        ]);

        if ($validated['type'] === 'text') {
            ChatPerm::create([
                'visibility' => 'viewer',
                'modify' => 'viewer',
                'notify' => true,
                'delete' => 'viewer',
                'allow_ai' => false,
                'chat_id' => $chat->id,
            ]);
        }

        return response()->json($chat, 201);
    }

    public function show(Chat $chat)
    {
        Gate::authorize('view', $chat);

        $chat->load(['messages' => function ($query) {
            $query->orderBy('created_at', 'desc')
                ->limit(50)
                ->with('user:id,name');
        }]);

        return response()->json($chat);
    }

    public function update(Request $request, Chat $chat)
    {
        Gate::authorize('update', $chat);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:text,voice',
        ]);

        $chat->update([
            'name' => $validated['name'],
        ]);

        return response()->json($chat);
    }

    public function destroy(Chat $chat)
    {
        Gate::authorize('delete', $chat);

        $chat->delete();

        return response()->json(['success' => true]);
    }
}
