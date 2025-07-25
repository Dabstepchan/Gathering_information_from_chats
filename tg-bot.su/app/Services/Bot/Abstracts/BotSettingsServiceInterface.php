<?php

namespace App\Services\Bot\Abstracts;

use App\Models\BotSettings;
use App\DTO\BotSettingsDTO;

interface BotSettingsServiceInterface
{
    public function updateBotSettings(array $data): bool;

    public function getBotSettings(): ?BotSettings;

    public function getBotSettingsAsDTO(): BotSettingsDTO;
}
