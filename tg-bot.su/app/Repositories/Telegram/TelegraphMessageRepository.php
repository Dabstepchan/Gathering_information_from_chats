<?php

namespace App\Repositories\Telegram;

use App\Models\TelegraphMessage;
use App\Repositories\Telegram\Abstracts\TelegraphMessageRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TelegraphMessageRepository implements TelegraphMessageRepositoryInterface
{
    public function createMessage(array $data): TelegraphMessage
    {
        return TelegraphMessage::create($data);
    }

    public function getMessagesWithHashtag(int $chatId, string $hashtag, Carbon $startDate, Carbon $endDate): Collection
    {
        return TelegraphMessage::where('telegraph_chat_id', $chatId)
            ->where('message', 'like', "%$hashtag%")
            ->whereBetween('sent_at', [$startDate, $endDate])
            ->get();
    }

    public function getDistinctChatIds(): Collection
    {
        return TelegraphMessage::distinct('telegraph_chat_id')
            ->pluck('telegraph_chat_id');
    }

    public function getDistinctGroupChatIds(): Collection
    {
        return TelegraphMessage::join('telegraph_chats', 'telegraph_messages.telegraph_chat_id', '=', 'telegraph_chats.id')
            ->where('telegraph_chats.chat_id', '<', 0)
            ->distinct('telegraph_chat_id')
            ->pluck('telegraph_chat_id');
    }

    public function getMessagesByChat(int $chatId): Collection
    {
        return TelegraphMessage::where('telegraph_chat_id', $chatId)->get();
    }
}
