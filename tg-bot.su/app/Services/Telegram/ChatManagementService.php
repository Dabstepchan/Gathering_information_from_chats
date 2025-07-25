<?php

namespace App\Services\Telegram;

use App\Models\TelegraphMessage;
use App\Repositories\Telegram\Abstracts\TelegraphChatRepositoryInterface;
use App\Services\Chat\Abstracts\ChatNameFormatterServiceInterface;
use App\Services\Telegram\Abstracts\ChatManagementServiceInterface;
use DefStudio\Telegraph\DTO\User;

class ChatManagementService implements ChatManagementServiceInterface
{
    public function __construct(
        private readonly TelegraphChatRepositoryInterface $chatRepository,
        private readonly ChatNameFormatterServiceInterface $chatNameFormatter
    ) {}

    public function processBotJoined($chat, User $member): void
    {
        if (!$member->isBot()) {
            return;
        }

        $this->cleanChatName($chat);

        $this->handleBotJoinedChat($chat->chat_id, $chat->name);
    }

    public function processBotLeft($chat, User $member): bool
    {
        if (!$member->isBot()) {
            return false;
        }

        $currentBot = $chat->bot;

        if (!$currentBot) {
            return false;
        }

        $botIdFromToken = explode(':', $currentBot->token)[0];

        if ((string)$member->id() === $botIdFromToken) {
            $this->handleBotLeftChat($chat->chat_id, $member);
            return true;
        } else {
            return false;
        }
    }

    public function cleanChatName($chat): bool
    {
        if (!$chat->name) {
            return false;
        }

        $cleanName = $this->chatNameFormatter->formatChatName($chat->name);

        if ($cleanName !== $chat->name) {
            $chat->name = $cleanName;
            return $chat->save();
        }

        return false;
    }

    public function handleBotJoinedChat(int $telegramChatId, ?string $chatName = null): void
    {
        $existingChat = $this->chatRepository->findChatByChatId($telegramChatId);

        if ($existingChat) {
            if ($chatName && $existingChat->name !== $chatName) {
                $cleanChatName = $this->chatNameFormatter->formatChatName($chatName);
                $this->chatRepository->updateChatName($existingChat->id, $cleanChatName);
            }
            return;
        }

        if ($chatName) {
            $this->chatNameFormatter->formatChatName($chatName);
        }
    }

    public function handleBotLeftChat(int $telegramChatId, User $botUser): void
    {
        $chat = $this->chatRepository->findChatByChatId($telegramChatId);

        if (!$chat) {
            return;
        }

        $this->cleanupChatData($chat->id);

        $this->chatRepository->deleteChatById($chat->id);
    }

    private function cleanupChatData(int $chatId): void
    {
        try {
            TelegraphMessage::where('telegraph_chat_id', $chatId)->delete();
        } catch (\Exception) {
        }
    }

    public function isChatTracked(int $telegramChatId): bool
    {
        return $this->chatRepository->findChatByChatId($telegramChatId) !== null;
    }
}
