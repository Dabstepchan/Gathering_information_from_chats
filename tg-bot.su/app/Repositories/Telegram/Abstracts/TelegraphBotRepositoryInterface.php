<?php

namespace App\Repositories\Telegram\Abstracts;

use DefStudio\Telegraph\Models\TelegraphBot;

interface TelegraphBotRepositoryInterface
{
    public function getFirstBot(): ?TelegraphBot;

    public function findBotById(int $botId): ?TelegraphBot;
}
