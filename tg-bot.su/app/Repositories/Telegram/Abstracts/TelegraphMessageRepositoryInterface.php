<?php

namespace App\Repositories\Telegram\Abstracts;

use App\Models\TelegraphMessage;
use Carbon\Carbon;
use Illuminate\Support\Collection;

interface TelegraphMessageRepositoryInterface
{
    public function createMessage(array $data): TelegraphMessage;

    public function getMessagesWithHashtag(int $chatId, string $hashtag, Carbon $startDate, Carbon $endDate): Collection;

    public function getDistinctChatIds(): Collection;

    public function getDistinctGroupChatIds(): Collection;

    public function getMessagesByChat(int $chatId): Collection;
}
