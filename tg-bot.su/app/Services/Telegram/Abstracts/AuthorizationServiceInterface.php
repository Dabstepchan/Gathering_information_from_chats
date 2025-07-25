<?php

namespace App\Services\Telegram\Abstracts;

interface AuthorizationServiceInterface
{
    public function isAdmin(?\DefStudio\Telegraph\DTO\User $user): bool;
}