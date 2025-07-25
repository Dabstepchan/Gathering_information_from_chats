<?php

namespace App\Services\Bot;

use App\DTO\BotSettingsDTO;
use App\Models\BotSettings;
use App\Repositories\Bot\Abstracts\BotSettingsRepositoryInterface;
use App\Services\Bot\Abstracts\BotSettingsServiceInterface;

class BotSettingsService implements BotSettingsServiceInterface
{
    public function __construct(
        private readonly BotSettingsRepositoryInterface $botSettingsRepository
    ) {}

    public function updateBotSettings(array $data): bool
    {
        return $this->botSettingsRepository->updateSettings($data);
    }

    public function getBotSettings(): ?BotSettings
    {
        return $this->botSettingsRepository->first();
    }

    public function getBotSettingsAsDTO(): BotSettingsDTO
    {
        $settings = $this->botSettingsRepository->firstOrCreate();
        return BotSettingsDTO::fromArray($settings->toArray());
    }
}
