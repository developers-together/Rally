<?php

namespace App\Policies;

use App\Models\Chat;
use App\Models\Team;
use App\Models\User;

class ChatPolicy
{
    public function viewAny(User $user, Team $team): bool
    {
        return $user->teams()->wherePivot('team_id', $team->id)->exists();
    }

    public function view(User $user, Chat $chat): bool
    {
        return $this->checkPerm($user, $chat, 'visibility');
    }

    public function create(User $user, Team $team): bool
    {
        return $user->teams()->wherePivot('team_id', $team->id)->exists();
    }

    public function update(User $user, Chat $chat): bool
    {
        return $this->checkPerm($user, $chat, 'modify');
    }

    public function delete(User $user, Chat $chat): bool
    {
        return $this->checkPerm($user, $chat, 'delete');
    }

    public function restore(User $user, Chat $chat): bool
    {
        return false;
    }

    public function forceDelete(User $user, Chat $chat): bool
    {
        return false;
    }

    private function checkPerm(User $user, Chat $chat, string $field): bool
    {
        $role = $this->getTeamRole($user, $chat);
        if ($role === null) {
            return false;
        }

        if (in_array($role, ['admin', 'owner'], true)) {
            return true;
        }

        $requiredRole = $chat->perm?->{$field};
        if ($requiredRole === null) {
            return false;
        }

        return match ($requiredRole) {
            'viewer' => true,
            'member' => $role === 'member',
            'admin' => false,
            default => false,
        };
    }

    private function getTeamRole(User $user, Chat $chat): ?string
    {
        return $chat->team->users()
            ->wherePivot('user_id', $user->id)
            ->first()?->pivot?->role;
    }
}
