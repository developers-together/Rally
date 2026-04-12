<?php

namespace App\Policies;

use App\Models\TaskList;
use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskListPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user, Team $team): bool
    {
        return $user->teams()->wherePivot('team_id', $team->id)->exists();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TaskList $taskList): bool
    {
        return $this->isMember($user, $taskList);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Team $team): bool
    {
        return $user->teams()->wherePivot('team_id', $team->id)->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TaskList $taskList): bool
    {
        return $this->isMember($user, $taskList);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TaskList $taskList): bool
    {
        return $this->isMember($user, $taskList);
    }

    private function isMember(User $user, TaskList $list): bool
    {
        if (! $list->team) {
            return false;
        }
        return $user->teams()->wherePivot('team_id', $list->team->id)->exists();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TaskList $taskList): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TaskList $taskList): bool
    {
        return false;
    }
}
