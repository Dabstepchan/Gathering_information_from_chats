<?php

namespace App\Presentation\Formatters;

use App\DTO\BotSettingsDTO;

class BotSettingsFormatter
{
    public static function format(BotSettingsDTO $settings): array
    {
        return [
            'report_day' => $settings->reportDay,
            'report_time' => $settings->reportTime,
            'period_weeks' => $settings->periodWeeks,
            'hashtags' => $settings->hashtags,
            'awaiting_input' => $settings->awaitingInput,
        ];
    }
}