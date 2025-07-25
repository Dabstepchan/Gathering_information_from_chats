<?php
namespace App\Services\Telegram;

use App\Services\Telegram\Abstracts\ReportCommandServiceInterface;
use App\Services\Bot\Abstracts\BotSettingsServiceInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;

class ReportCommandService implements ReportCommandServiceInterface
{
    public function __construct(
        private readonly BotSettingsServiceInterface $botSettingsService
    ) {}

    public function showReportMenu($chat, int $messageId): void
    {
        $keyboard = Keyboard::make()
            ->row([Button::make('📊 Отчет Менеджер-Клиент')->action('generate_report')])
            ->row([Button::make('⬅️ Назад')->action('showMainMenu_copy')]);

        $chat->edit($messageId)
            ->message('Меню отчетов:')
            ->keyboard($keyboard)
            ->send();
    }

    public function generateReport($chat): void
    {
        $settings = $this->botSettingsService->getBotSettings();
        $timezone = config('telegram.timezone', 'Asia/Novokuznetsk');
        $now = Carbon::now($timezone);
        $periodWeeks = $settings->period_weeks ?? 1;

        $startOfPeriod = $now->copy()->subWeeks($periodWeeks);
        $endOfPeriod = $now->copy();

        Artisan::call('reports:check', [
            '--start' => $startOfPeriod->toDateTimeString(),
            '--end' => $endOfPeriod->toDateTimeString(),
        ]);

        $chat->message('Отчет успешно сгенерирован')->send();
    }
}