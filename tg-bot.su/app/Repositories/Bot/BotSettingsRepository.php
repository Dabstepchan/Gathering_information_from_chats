<?php

namespace App\Repositories\Bot;

use App\Models\BotSettings;
use App\Repositories\Bot\Abstracts\BotSettingsRepositoryInterface;

class BotSettingsRepository implements BotSettingsRepositoryInterface
{
    public function first(): ?BotSettings
    {
        return BotSettings::first();
    }

    public function firstOrCreate(): BotSettings
    {
        $settings = $this->first();

        if (!$settings) {
            $settings = new BotSettings();
            $settings->save();
        }

        return $settings;
    }

    public function updateSettings(array $data): bool
    {
        $settings = $this->firstOrCreate();
        $settings->fill($data);
        return $settings->save();
    }

    public function getHashtags(): array
    {
        $settings = $this->first();

        return $settings->hashtags ?? [
            '#митрепорт' => 'Тут не было митрепортов',
            '#еженедельныйотчет' => 'Тут не было еж.отчетов',
        ];
    }
}
