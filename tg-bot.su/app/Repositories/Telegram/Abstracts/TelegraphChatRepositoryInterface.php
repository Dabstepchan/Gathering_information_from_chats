<?php

namespace App\Repositories\Telegram\Abstracts;

use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Collection;

interface TelegraphChatRepositoryInterface
{
    public function getChatsByBotId(int $botId): Collection;

    public function findChatById(int $chatId): ?TelegraphChat;

    public function getAllChats(): Collection;

    public function deleteChatById(int $chatId): bool;

    public function findChatByChatId(int $telegramChatId): ?TelegraphChat;

    public function updateChatName(int $chatId, string $newName): bool;
}
