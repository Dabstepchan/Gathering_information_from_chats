<?php

namespace App\Services\Report;

use App\DTO\BotSettingsDTO;
use App\DTO\ReportPeriodDTO;
use App\Enums\WeekDay;
use Carbon\Carbon;
use App\Services\Report\Abstracts\PeriodCalculationServiceInterface;

class PeriodCalculationService implements PeriodCalculationServiceInterface
{
    public function calculateReportPeriod(
        BotSettingsDTO $settings, 
        ?Carbon $startDate = null, 
        ?Carbon $endDate = null
    ): ReportPeriodDTO {
        $timezone = config('telegram.timezone', 'Asia/Novokuznetsk');
        
        if ($startDate && $endDate) {
            return new ReportPeriodDTO($startDate, $endDate, $timezone);
        }

        return $this->calculatePeriodFromSettings($settings);
    }

    public function calculatePeriodFromSettings(BotSettingsDTO $settings): ReportPeriodDTO
    {
        $timezone = config('telegram.timezone', 'Asia/Novokuznetsk');
        $now = Carbon::now($timezone);
        $weekDay = WeekDay::fromRussian($settings->reportDay);
        $reportDay = $weekDay ? $weekDay->englishName() : 'Monday';
        $reportTime = $settings->reportTime;
        $periodWeeks = $settings->periodWeeks;

        $timeParts = explode(':', $reportTime);

        if ($now->format('H:i:s') < $reportTime && 
            $now->dayOfWeek === Carbon::parse($reportDay)->dayOfWeek) {
            $endOfPeriod = $now->copy()
                ->subWeek()
                ->next($reportDay)
                ->setTime($timeParts[0], $timeParts[1], 0)
                ->subSecond();
        } else {
            $endOfPeriod = $now->copy()
                ->previous($reportDay)
                ->setTime($timeParts[0], $timeParts[1], 0)
                ->subSecond();
        }

        $startOfPeriod = $endOfPeriod->copy()
            ->subWeeks($periodWeeks)
            ->addSecond();

        return new ReportPeriodDTO($startOfPeriod, $endOfPeriod, $timezone);
    }

    public function calculateSimplePeriod(int $periodWeeks, string $timezone = 'UTC'): ReportPeriodDTO
    {
        $now = Carbon::now($timezone);
        $startOfPeriod = $now->copy()->subWeeks($periodWeeks);
        $endOfPeriod = $now->copy();

        return new ReportPeriodDTO($startOfPeriod, $endOfPeriod, $timezone);
    }
}