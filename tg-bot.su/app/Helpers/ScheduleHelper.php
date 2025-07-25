<?php

namespace App\Helpers;

use Illuminate\Support\Carbon;
use App\Enums\WeekDay;

class ScheduleHelper
{
    public static function shouldRunReports(string $reportDay, string $reportTime, string $timezone = 'Asia/Novokuznetsk'): bool
    {
        $now = Carbon::now($timezone);
        
        $currentDay = $now->format('l');
        $weekDay = WeekDay::fromRussian($reportDay);
        
        if (!$weekDay) {
            return false;
        }
        
        return $weekDay->englishName() === $currentDay && self::isWithinTimeFrame($reportTime, $now);
    }

    public static function isWithinTimeFrame(string $scheduledTime, Carbon $now, int $toleranceSeconds = 5): bool
    {
        $scheduledTime = trim($scheduledTime);
    
        if (!preg_match('/^\d{2}:\d{2}:\d{2}$/', $scheduledTime)) {
            return false;
        }
    
        $scheduledDateTime = Carbon::createFromFormat('H:i:s', $scheduledTime, $now->timezone);
    
        $startTime = $scheduledDateTime->copy()->subSeconds($toleranceSeconds);
        $endTime = $scheduledDateTime->copy()->addSeconds($toleranceSeconds);
    
        return $now->between($startTime, $endTime);
    }
}