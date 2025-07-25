<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Bot\Abstracts\BotSettingsServiceInterface;
use App\Helpers\ScheduleHelper;

class CheckSchedule extends Command
{
    protected $signature = 'schedule:check-reports';
    protected $description = 'Проверяет, пришло ли время запуска отчетов на основе настроек';

    public function __construct(
        private readonly BotSettingsServiceInterface $botSettingsService
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $timezone = config('telegram.timezone', 'Asia/Novokuznetsk');

        $settings = $this->botSettingsService->getBotSettingsAsDTO();

        if (ScheduleHelper::shouldRunReports($settings->reportDay, $settings->reportTime, $timezone)) {
            $this->info('Запуск проверки отчетов...');
            $this->call('reports:check');
        }
    }
}
