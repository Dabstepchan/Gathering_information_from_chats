<?php

namespace App\Presentation\Formatters;

use App\DTO\ReportPeriodDTO;

class ReportPeriodFormatter
{
    public static function formatLogMessage(ReportPeriodDTO $period): string
    {
        return "Проверка сообщений за период:\n" .
               "Начало периода: " . $period->startDate->toDateTimeString() . "\n" .
               "Конец периода: " . $period->endDate->toDateTimeString();
    }
}
