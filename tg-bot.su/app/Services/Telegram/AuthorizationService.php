<?php

namespace App\Services\Telegram;

use App\Services\Telegram\Abstracts\AuthorizationServiceInterface;

class AuthorizationService implements AuthorizationServiceInterface
{
    public function isAdmin(?\DefStudio\Telegraph\DTO\User $user): bool
    {
        if (!$user) {
            return false;
        }

        $adminUserId = config('telegram.admin_user_id');
        return (string)$user->id() === $adminUserId;
    }
}