<?php

namespace App\Repositories\Bot\Abstracts;

use App\Models\BotSettings;

interface BotSettingsRepositoryInterface
{
    public function first(): ?BotSettings;

    public function firstOrCreate(): BotSettings;

    public function updateSettings(array $data): bool;

    public function getHashtags(): array;
}
