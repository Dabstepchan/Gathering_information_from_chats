<?php

namespace App\Services\Telegram\Abstracts;

use DefStudio\Telegraph\DTO\User;

interface ChatManagementServiceInterface
{
    public function processBotJoined($chat, User $member): void;
    public function processBotLeft($chat, User $member): bool;
    public function cleanChatName($chat): bool;
    public function handleBotJoinedChat(int $telegramChatId, ?string $chatName = null): void;
    public function handleBotLeftChat(int $telegramChatId, User $botUser): void;
    public function isChatTracked(int $telegramChatId): bool;
}
