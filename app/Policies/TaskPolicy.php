<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\TaskList;
use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
 
    public function viewAny(User $user, TaskList $list): bool
    {
        if (! $list->team) {
            return false;
        }
        return $user->teams()->wherePivot('team_id', $list->team->id)->exists();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): bool
    {
        return $this->isMember($user, $task);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, TaskList $list): bool
    {
        if (! $list->team) {
            return false;
        }
        return $list->team->users()->wherePivot('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): bool
    {
        return $this->isMember($user, $task);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): bool
    {
        return $this->isMember($user, $task);
    }

    /** Returns false instead of throwing if FK chain is broken (orphaned task/list). */
    private function isMember(User $user, Task $task): bool
    {
        $team = $task->taskList?->team;
        if (! $team) {
            return false;
        }
        return $team->users()->wherePivot('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Task $task): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Task $task): bool
    {
        return false;
    }
}
