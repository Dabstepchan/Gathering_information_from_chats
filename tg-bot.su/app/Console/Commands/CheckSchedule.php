<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use App\Models\BotSettings;

class CheckSchedule extends Command
{
    protected $signature = 'schedule:check-reports';
    protected $description = 'Проверяет, пришло ли время запуска отчетов на основе настроек';

    public function handle()
    {
        $settings = BotSettings::first();
        if (!$settings) {
            $this->error('Настройки не найдены');
            return;
        }

        $timezone = 'Asia/Novokuznetsk';
        $now = Carbon::now($timezone);
        
        $scheduledDay = $settings->report_day;
        $scheduledTime = $settings->report_time;
        
        $currentDay = $now->format('l');
        
        if ($this->translateDayToEnglish($scheduledDay) === $currentDay && $this->isWithinTimeFrame($scheduledTime, $now)) {
            $this->info('Запуск проверки отчетов...');
            $this->call('reports:check');
        }
    }

    protected function translateDayToEnglish($day)
    {
        $translations = [
            'Понедельник' => 'Monday',
            'Вторник' => 'Tuesday',
            'Среда' => 'Wednesday',
            'Четверг' => 'Thursday',
            'Пятница' => 'Friday',
            'Суббота' => 'Saturday',
            'Воскресенье' => 'Sunday',
        ];

        return $translations[$day] ?? $day;
    }

    protected function isWithinTimeFrame($scheduledTime, Carbon $now)
    {
        $scheduledTime = trim($scheduledTime);
    
        if (!preg_match('/^\d{2}:\d{2}:\d{2}$/', $scheduledTime)) {
            $this->error('Неверный формат времени: ' . $scheduledTime);
            return false;
        }
    
        $scheduledDateTime = Carbon::createFromFormat('H:i:s', $scheduledTime, $now->timezone);
    
        $startTime = $scheduledDateTime->copy()->subSeconds(5);
        $endTime = $scheduledDateTime->copy()->addSeconds(5);
    
        return $now->between($startTime, $endTime);
    }    
}
