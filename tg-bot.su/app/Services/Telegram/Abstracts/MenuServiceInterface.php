<?php

namespace App\Services\Telegram\Abstracts;

interface MenuServiceInterface
{
    public function showMainMenu($chat): void;
    public function editToMainMenu($chat, int $messageId): void;
}