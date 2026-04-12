<?php
namespace App\Policies;

use App\Models\Chat;
use App\Models\User;

class MessagePolicy
{
    public function delete(User $user, Chat $chat): bool
    {
        return $this->canUsePermission($user, $chat, 'delete');
    }

    public function sendMessage(User $user, Chat $chat): bool
    {
        return $this->canUsePermission($user, $chat, 'write');
    }

    public function update(User $user, Chat $chat): bool
    {
        return $this->canUsePermission($user, $chat, 'modify');
    }

    public function notify(User $user, Chat $chat): bool
    {
        $role = $this->getTeamRole($user, $chat);
        if ($role === null) {
            return false;
        }

        return (bool) $chat->perm?->notify;
    }


    public function getMessages(User $user, Chat $chat): bool
    {
        return $this->canUsePermission($user, $chat, 'visibility');
    }

    private function canUsePermission(User $user, Chat $chat, string $field): bool
    {
        $role = $this->getTeamRole($user, $chat);
        if ($role === null) {
            return false;
        }

        if ($this->isAdminOrOwner($role)) {
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

    private function isAdminOrOwner(?string $role): bool
    {
        return in_array($role, ['admin', 'owner'], true);
    }
}
