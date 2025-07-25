<?php

namespace App\Services\Telegram;

use App\Services\Telegram\Abstracts\SettingsCommandServiceInterface;
use App\Services\Bot\Abstracts\BotSettingsServiceInterface;
use App\Presentation\Formatters\BotSettingsFormatter;
use App\Enums\WeekDay;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;

class SettingsCommandService implements SettingsCommandServiceInterface
{
    public function __construct(
        private readonly BotSettingsServiceInterface $botSettingsService
    ) {}

    public function showSettings($chat, int $messageId): void
    {
        $keyboard = Keyboard::make()
            ->row([Button::make('📋 Отчеты Менеджер-Клиент')->action('settings_reports')])
            ->row([Button::make('⬅️ Назад')->action('showMainMenu_copy')]);

        $chat->edit($messageId)
            ->message('Меню настроек:')
            ->keyboard($keyboard)
            ->send();
    }

    public function showReportSettings($chat, int $messageId): void
    {
        $settings = $this->botSettingsService->getBotSettingsAsDTO();
        $formattedSettings = BotSettingsFormatter::format($settings);
        $message = $this->formatSettingsMessage($formattedSettings);

        $keyboard = Keyboard::make()
            ->row([Button::make('📅 Изменить день')->action('set_report_day')])
            ->row([Button::make('⏰ Изменить время')->action('set_report_time')])
            ->row([Button::make('📊 Изменить период')->action('set_period_weeks')])
            ->row([Button::make('🏷 Управление хештегами')->action('manage_hashtags')])
            ->row([Button::make('⬅️ Назад')->action('settings')]);

        $chat->edit($messageId)
            ->message($message)
            ->keyboard($keyboard)
            ->send();
    }

    public function showDaySelector($chat, int $messageId): void
    {
        $keyboard = Keyboard::make();
        
        foreach (WeekDay::all() as $weekDay) {
            $keyboard->row([
                Button::make($weekDay->label())
                    ->action('save_report_day')
                    ->param('day', $weekDay->value)
            ]);
        }
        
        $keyboard->row([Button::make('⬅️ Назад')->action('settings_reports')]);

        $chat->edit($messageId)
            ->message('Выберите день недели для сбора отчета:')
            ->keyboard($keyboard)
            ->send();
    }

    public function saveReportDay(string $day, $chat): void
    {
        $weekDay = WeekDay::from($day);
        $dayName = $weekDay->label();

        $this->botSettingsService->updateBotSettings(['report_day' => $dayName]);
        $chat->message("День сбора отчета установлен на: $dayName")->send();
    }

    public function showTimeSelector($chat, int $messageId): void
    {
        $keyboard = Keyboard::make();
        $workingHours = range(9, 17);
        
        foreach ($workingHours as $hour) {
            $displayTime = sprintf('%02d:00', $hour);
            $keyboard->row([
                Button::make($displayTime)
                    ->action('save_report_time')
                    ->param('time', $hour)
            ]);
        }
        
        $keyboard->row([Button::make('⬅️ Назад')->action('settings_reports')]);

        $chat->edit($messageId)
            ->message('Выберите время сбора отчета:')
            ->keyboard($keyboard)
            ->send();
    }

    public function saveReportTime(string $hour, $chat): void
    {
        if (!is_numeric($hour) || $hour < 0 || $hour > 23) {
            $chat->message('Ошибка: недопустимое значение времени')->send();
            return;
        }

        $time = sprintf('%02d:00:00', (int)$hour);
        $this->botSettingsService->updateBotSettings(['report_time' => $time]);
        $chat->message("Время сбора отчета установлено на: $time")->send();
    }

    public function showPeriodSelector($chat, int $messageId): void
    {
        $keyboard = Keyboard::make();
        $periods = [1, 2, 3, 4];
        
        foreach ($periods as $weeks) {
            $keyboard->row([
                Button::make("$weeks " . $this->getWeekWord($weeks))
                    ->action('save_period_weeks')
                    ->param('weeks', $weeks)
            ]);
        }
        
        $keyboard->row([Button::make('⬅️ Назад')->action('settings_reports')]);

        $chat->edit($messageId)
            ->message('Выберите период сбора отчета:')
            ->keyboard($keyboard)
            ->send();
    }

    public function savePeriodWeeks(string $weeks, $chat): void
    {
        $weeksInt = (int)$weeks;
        $this->botSettingsService->updateBotSettings(['period_weeks' => $weeksInt]);
        $chat->message("Период сбора установлен на: $weeksInt " . $this->getWeekWord($weeksInt))->send();
    }

    private function formatSettingsMessage(array $settings): string
    {
        return "📋 Настройки отчетов:\n\n" .
               "📅 День: {$settings['report_day']}\n" .
               "⏰ Время: {$settings['report_time']}\n" .
               "📊 Период: {$settings['period_weeks']} " . $this->getWeekWord($settings['period_weeks']) . "\n" .
               "🏷 Хештегов: " . count($settings['hashtags']);
    }

    private function getWeekWord(int $number): string
    {
        $lastDigit = $number % 10;
        $lastTwoDigits = $number % 100;
        
        if ($lastDigit === 1 && $lastTwoDigits !== 11) {
            return 'неделя';
        }
        if ($lastDigit >= 2 && $lastDigit <= 4 && ($lastTwoDigits < 12 || $lastTwoDigits > 14)) {
            return 'недели';
        }
        return 'недель';
    }
}