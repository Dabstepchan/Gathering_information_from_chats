<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use App\Services\Report\Abstracts\HashtagReportServiceInterface;
use App\Services\Report\Abstracts\PeriodCalculationServiceInterface;
use App\Services\Bot\Abstracts\BotSettingsServiceInterface;
use App\Presentation\Formatters\ReportPeriodFormatter;

class CheckHashtagReports extends Command
{
    protected $signature = 'reports:check {--start=} {--end=}';
    protected $description = 'Проверка хештегов в чатах';

    public function __construct(
        private readonly HashtagReportServiceInterface $hashtagReportService,
        private readonly PeriodCalculationServiceInterface $periodCalculationService,
        private readonly BotSettingsServiceInterface $botSettingsService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $timezone = config('telegram.timezone', 'Asia/Novokuznetsk');

        $startOfPeriod = null;
        $endOfPeriod = null;

        if ($this->option('start') && $this->option('end')) {
            $startOfPeriod = Carbon::parse($this->option('start'), $timezone);
            $endOfPeriod = Carbon::parse($this->option('end'), $timezone);
        }

        try {
            $this->info("Запуск проверки хештегов...");

            $settings = $this->botSettingsService->getBotSettingsAsDTO();

            $period = $this->periodCalculationService->calculateReportPeriod($settings, $startOfPeriod, $endOfPeriod);
            $this->info(ReportPeriodFormatter::formatLogMessage($period));

            $this->hashtagReportService->generateReport($startOfPeriod, $endOfPeriod);
            $this->info("Проверка завершена успешно.");
        } catch (Exception $e) {
            $this->error("Ошибка при выполнении проверки: " . $e->getMessage());
            return null;
        }
    }
}
