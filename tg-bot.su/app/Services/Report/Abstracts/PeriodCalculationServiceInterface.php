<?php

namespace App\Services\Report\Abstracts;

use App\DTO\BotSettingsDTO;
use App\DTO\ReportPeriodDTO;
use Illuminate\Support\Carbon;

interface PeriodCalculationServiceInterface
{
    public function calculateReportPeriod(BotSettingsDTO $settings, ?Carbon $startDate = null, ?Carbon $endDate = null): ReportPeriodDTO;
    
    public function calculatePeriodFromSettings(BotSettingsDTO $settings): ReportPeriodDTO;
    
    public function calculateSimplePeriod(int $periodWeeks, string $timezone = 'UTC'): ReportPeriodDTO;
}