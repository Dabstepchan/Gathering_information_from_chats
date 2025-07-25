<?php

namespace App\Repositories\Telegram;

use App\Repositories\Telegram\Abstracts\TelegraphBotRepositoryInterface;
use DefStudio\Telegraph\Models\TelegraphBot;

class TelegraphBotRepository implements TelegraphBotRepositoryInterface
{
    public function getFirstBot(): ?TelegraphBot
    {
        return TelegraphBot::first();
    }

    public function findBotById(int $botId): ?TelegraphBot
    {
        return TelegraphBot::find($botId);
    }
}
