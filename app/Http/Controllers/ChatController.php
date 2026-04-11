<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chat;
use App\Models\User;
use App\Models\ChatPerm;
use App\Models\Team;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ChatController extends Controller
{
    use AuthorizesRequests;

    // Display all chats
    public function index(Team $team)
    {
        $user = Auth::user();

        // Gate::authorize('viewAny', $user, $team);

        if($team->users()->wherePivot('user_id',$user->id)->wherePivotIn('role',['admin','owner']))
        {
            $chats = Chat::where('team_id', $team->id)->with(['messages'=> function($query){
                $query->orderBy('created_at', 'desc')->limit(30);
                if($query->path->exists())
                    $query->path = file($query->path);
                if($query->reply_to->exists)
                    $query->reply_to = User::where('id', $query->reply_to);

            }])->get();

            return Inertia::render('chat',['data' =>$chats]);
        }



        if($team->users()->wherePivot('user_id',$user->id)->wherePivot('role','member'))
        {

        $chats = Chat::where('team_id', $team->id)->whereRelation('perm','visibility', 'viewer')
                ->orWhereRelation('perm','visibility','member')
                ->with(['messages'=> function($query){
                    $query->orderBy('created_at','desc')->limit(30);
                    if($query->path->exists())
                        $query->path = file($query->path);
                    if($query->reply_to->exists)
                        $query->reply_to = User::where('id', $query->reply_to);
                }])->get();

            return Inertia::render('chat',['data'=>$chats]);
        }

        if($team->users()->wherePivot('user_id',$user->id)->wherePivot('role','viewer'))
        {
                $chats = Chat::where('team_id', $team->id)->whereRelation('perm','visibility', 'viewer')
                ->with(['messages'=> function($query){
                    $query->orderBy('created_at','desc')->limit(30);
                    if($query->path->exists())
                        $query->path = file($query->path);
                    if($query->reply_to->exists)
                        $query->reply_to = User::where('id', $query->reply_to);
                }])->get();

            return Inertia::render('chat',['data'=>$chats]);

        }

        return back()->with(['error' => 'connot retrieve chats']);
    }


    public function store(Request $request, Team $team)
    {
        $user = Auth::user();

        Gate::authorize('create', $user, $team);

       $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:text,voice'
        ]);

            $chat = Chat::create([
                'name'=> $validated['name'],
                'type'=> $validated['type'],
                'team_id' => $team->id
                ]);

        if($validated['type'] == 'text'){

             ChatPerm::create([

            'visibility'=> 'viewer',
            'modify' => 'viewer',
            'notify' => true,
            'delete' => 'viewer',
            'allow_ai' => false,
            'chat_id' => $chat->id
        ]);

        }

        return $chat->toJson();
    }

    public function show(Chat $chat)
    {
        // Authorize the action
        Gate::authorize('view',Auth::user(), $chat);

        $chat= $chat->with(['messages'=> function($query){
            $query->orderby('created_at')->pagenate(50);
            if($query->path->exists())
            $query->path = file($query->path);
            if($query->reply_to->exists)
                $query->reply_to = User::where('id', $query->reply_to);
        }]);
        // Return the chat details as a JSON response
        return $chat->toJson();
    }

    public function update(Request $request , Chat $chat)
    {
        $user = Auth::user();
        Gate::authorize('update',$user, $chat);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:text,voice'

        ]);


        if($chat->team->users()->where('user_id', $user->id)){

        $chat->update([
            'name'=> $validated['name'],
        ]);
    }

        return $chat->toJson();
    }

    public function destroy(Chat $chat, Request $request)
    {
        $user = Auth::user();
        Gate::authorize('delete',$user, $chat);

        $validated = $request->validate([
            'chat_id' => 'required|exists:chats,id',
        ]);

        $chat = Chat::find($validated['chat_id']);

        if (!$chat) {
            return response()->json(['success' => false, 'message' => 'Chat not found'], 404);
        }

        $chat->delete();

        return response()->json(['success' => true]);
    }


}
