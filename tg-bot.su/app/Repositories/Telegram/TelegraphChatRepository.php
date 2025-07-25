<?php

namespace App\Repositories\Telegram;

use App\Repositories\Telegram\Abstracts\TelegraphChatRepositoryInterface;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Collection;

class TelegraphChatRepository implements TelegraphChatRepositoryInterface
{
    public function getChatsByBotId(int $botId): Collection
    {
        return TelegraphChat::where('telegraph_bot_id', $botId)->get();
    }

    public function findChatById(int $chatId): ?TelegraphChat
    {
        return TelegraphChat::find($chatId);
    }

    public function getAllChats(): Collection
    {
        return TelegraphChat::all();
    }

    public function deleteChatById(int $chatId): bool
    {
        $chat = $this->findChatById($chatId);

        if (!$chat) {
            return false;
        }

        return $chat->delete();
    }

    public function findChatByChatId(int $telegramChatId): ?TelegraphChat
    {
        return TelegraphChat::where('chat_id', $telegramChatId)->first();
    }

    public function updateChatName(int $chatId, string $newName): bool
    {
        $chat = $this->findChatById($chatId);

        if (!$chat) {
            return false;
        }

        $chat->name = $newName;
        return $chat->save();
    }
}
