<?php

namespace App\Policies;

use App\Models\Chat;
use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ChatPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user, Team $team): bool
    {
        // return $user->teams()->wherePivot('team_id',$team->id)->exists();
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Chat $chat): bool
    {
        if($chat->perm->visibility == 'viewer'){
        return $chat->team->users()->where('user_id', $user->id)->exists();
        }

        if($chat->team->user->where('user_id',$user->id)->role == 'admin' ||
           $chat->team->user->where('user_id',$user->id)->role == 'owner')
        return true;

        if($chat->perm->visibility == 'member'){

        return $chat->team->users()->where('user_id', $user->id)->where('role','member')->exists();
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Team $team): bool
    {
        return $user->teams()->wherePivot('team_id',$team->id)->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Chat $chat): bool
    {
        if($chat->perm->modify == 'viewer'){
        return $chat->team->users()->where('user_id', $user->id)->exists();
        }

        if($chat->team->user->where('user_id',$user->id)->role == 'admin' ||
           $chat->team->user->where('user_id',$user->id)->role == 'owner')
        return true;

        if($chat->perm->modify == 'member'){

        return $chat->team->users()->where('user_id', $user->id)->where('role','member')->exists();
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Chat $chat): bool
    {
        if($chat->perm->delete == 'viewer'){
        return $chat->team->users()->where('user_id', $user->id)->exists();
        }

        if($chat->team->user->where('user_id',$user->id)->role == 'admin' ||
           $chat->team->user->where('user_id',$user->id)->role == 'owner')
        return true;

        if($chat->perm->delete == 'member'){

        return $chat->team->users()->where('user_id', $user->id)->where('role','member')->exists();
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Chat $chat): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Chat $chat): bool
    {
        return false;
    }
}
