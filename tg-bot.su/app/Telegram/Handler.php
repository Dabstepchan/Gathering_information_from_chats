<?php

namespace App\Telegram;

use App\Models\TelegraphMessage;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Telegraph;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Keyboard\Button;
use Illuminate\Support\Carbon;
use Illuminate\Support\Stringable;
use App\Models\BotSettings;

class Handler extends WebhookHandler
{
    protected string $timezone = 'Asia/Novokuznetsk';

    protected const WEEKDAYS = [
        'Понедельник' => 1,
        'Вторник' => 2,
        'Среда' => 3,
        'Четверг' => 4,
        'Пятница' => 5,
        'Суббота' => 6,
        'Воскресенье' => 7
    ];

    //Обработка входящих сообщений
    protected function handleChatMessage(Stringable $text): void
    {
        $messageText = $text->toString();
    
        if (preg_match('/^#([^\s]+)\s+(.+)$/u', $messageText, $matches)) {
            $this->handleHashtagInput($messageText);
            return;
        }        
    
        TelegraphMessage::create([
            'telegraph_chat_id' => $this->chat->id,
            'message' => $messageText,
            'sent_at' => Carbon::now()->setTimezone($this->timezone),
        ]);
    }
    
    //Проверка прав администратора
    protected function isAdmin(): bool
    {
        $adminId = env('ADMIN_USER_ID');
        
        if ($this->callbackQuery !== null) {
            return (string)$this->callbackQuery->from()->id() === $adminId;
        }
        
        if ($this->message !== null) {
            return (string)$this->message->from()->id() === $adminId;
        }

        return false;
    }

    //Главное меню
    public function start(): void
    {
        if ($this->isAdmin()) {
            $this->showMainMenu();
        } else {
            $this->chat->message('У вас нет доступа к этому боту.')->send();
        }
    }
    
    //Главное меню
    public function showMainMenu(): void
    {
        $keyboard = Keyboard::make()
            ->row([
                Button::make('📊 Отчет')->action('generateReport'),
                Button::make('⚙️ Настройки')->action('settings'),
            ])
            ->row([
                Button::make('ℹ️ Информация')->action('info'),
            ]);
    
        $this->chat
            ->message('Выберите действие:')
            ->keyboard($keyboard)
            ->send(); 
    }     
    
    //Копия меню
    public function showMainMenu_copy(): void
    {
        $keyboard = Keyboard::make()
            ->row([
                Button::make('📊 Отчет')->action('generateReport'),
                Button::make('⚙️ Настройки')->action('settings'),
            ])
            ->row([
                Button::make('ℹ️ Информация')->action('info'),
            ]);
    
        $this->chat
            ->edit($this->messageId)
            ->message('Выберите действие:')
            ->keyboard($keyboard)
            ->send(); 
    }    

    //Настройки
    public function settings(): void
    {
        if (!$this->isAdmin()) {
            return;
        }

        $keyboard = Keyboard::make()
            ->row([Button::make('📋 Отчеты Менеджер-Клиент')->action('settings_reports')])
            ->row([Button::make('⬅️ Назад')->action('showMainMenu_copy')]);

        $this->chat
        ->edit($this->messageId)
        ->message('Меню настроек:')
            ->keyboard($keyboard)
            ->send();
    }

    //Создать отчёт
    public function generateReport(): void
    {
        if (!$this->isAdmin()) {
            return;
        }
    
        $keyboard = Keyboard::make()
            ->row([Button::make('📊 Отчет Менеджер-Клиент')->action('generate_report')])
            ->row([Button::make('⬅️ Назад')->action('showMainMenu_copy')]);
    
        $this->chat
        ->edit($this->messageId)
        ->message('Меню отчетов:')
            ->keyboard($keyboard)
            ->send();
    }

    //Информация
    public function info(): void
    {
        if (!$this->isAdmin()) {
            return;
        }
    
        $this->chat->message("Бот для сбора отчетов из клиентских чатов.")
            ->send();
    }

    //Настройки отчёта
    public function settings_reports(): void
    {
        if (!$this->isAdmin()) {
            return;
        }
    
        $settings = BotSettings::query()->first();
    
        $message = "Текущие настройки отчета Менеджер-Клиент:\n\n";
        $message .= "📅 День недели: " . ($settings->report_day ?? 'Понедельник') . "\n";
        $message .= "⏰ Время сбора: " . substr($settings->report_time ?? '10:00', 0, 5) . "\n";
        $message .= "📊 Период (недель): " . ($settings->period_weeks ?? '1') . "\n";
        $message .= "🏷 Хештеги:\n";
    
        $hashtags = $settings->hashtags ?? ['#митрепорт' => 'Тут не было митрепортов'];
    
        foreach ($hashtags as $tag => $title) {
            $message .= "   $tag -> $title\n";
        }
    
        $keyboard = Keyboard::make()
            ->row([Button::make('📅 Изменить день')->action('set_report_day')])
            ->row([Button::make('⏰ Изменить время')->action('set_report_time')])
            ->row([Button::make('📊 Изменить период')->action('set_period_weeks')])
            ->row([Button::make('🏷 Управление хештегами')->action('manage_hashtags')])
            ->row([Button::make('⬅️ Назад')->action('settings')]);
    
        $this->chat
        ->edit($this->messageId)
        ->message($message)
            ->keyboard($keyboard)
            ->send();
    }

    //Установить день отчёта
    public function set_report_day(): void
    {
        if (!$this->isAdmin()) {
            return;
        }

        $keyboard = Keyboard::make();
        foreach (self::WEEKDAYS as $day => $value) {
            $keyboard->row([
                Button::make($day)->action('save_report_day')->param('day', $value)
            ]);
        }
        $keyboard->row([Button::make('⬅️ Назад')->action('settings_reports')]);

        $this->chat
        ->edit($this->messageId)
        ->message('Выберите день недели для сбора отчета:')
            ->keyboard($keyboard)
            ->send();
    }

    //Сохранить
    public function save_report_day(): void
    {
        if (!$this->isAdmin()) {
            return;
        }

        $day = $this->data->get('day');
        $dayName = array_search($day, self::WEEKDAYS);

        $settings = BotSettings::first() ?? new BotSettings();
        $settings->report_day = $dayName;
        $settings->save();

        $this->chat->message("День сбора отчета установлен на: $dayName")
            ->send();

        $this->settings_reports();
    }

    //Установить время отчёта
    public function set_report_time(): void
    {
        if (!$this->isAdmin()) {
            return;
        }
    
        $keyboard = Keyboard::make();
        foreach ([9, 10, 11, 12, 13, 14, 15, 16, 17] as $hour) {
            $displayTime = sprintf('%02d:00', $hour);
            $keyboard->row([
                Button::make($displayTime)->action('save_report_time')->param('time', $hour)
            ]);
        }
        $keyboard->row([Button::make('⬅️ Назад')->action('settings_reports')]);
    
        $this->chat
        ->edit($this->messageId)
        ->message('Выберите время сбора отчета:')
            ->keyboard($keyboard)
            ->send();
    }
    
    //Сохранить
    public function save_report_time(): void
    {
        if (!$this->isAdmin()) {
            return;
        }
    
        $hour = $this->data->get('time');
        
        if (!is_numeric($hour) || $hour < 0 || $hour > 23) {
            $this->chat->message('Ошибка: недопустимое значение времени')
                ->send();
            $this->set_report_time();
            return;
        }
    
        $time = sprintf('%02d:00', (int)$hour);
        
        $settings = BotSettings::first() ?? new BotSettings();
        $settings->report_time = $time;
        $settings->save();
    
        $this->chat->message("Время сбора отчета установлено на: $time")
            ->send();
    
        $this->settings_reports();
    }
    
    //Формат времени
    protected function formatTime(string $time): string
    {
        if (preg_match('/^([0-9]{2}):([0-9]{2})$/', $time)) {
            return $time;
        }
        if (is_numeric($time)) {
            return sprintf('%02d:00', (int)$time);
        }
        return '10:00';
    }

    //Установить период недель
    public function set_period_weeks(): void
    {
        if (!$this->isAdmin()) {
            return;
        }

        $keyboard = Keyboard::make();
        foreach ([1, 2, 3, 4] as $weeks) {
            $keyboard->row([
                Button::make("$weeks " . $this->getWeekWord($weeks))->action('save_period_weeks')->param('weeks', $weeks)
            ]);
        }
        $keyboard->row([Button::make('⬅️ Назад')->action('settings_reports')]);

        $this->chat
        ->edit($this->messageId)
        ->message('Выберите период сбора отчета:')
            ->keyboard($keyboard)
            ->send();
    }

    //Склонение
    protected function getWeekWord(int $number): string
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

    //Сохранить
    public function save_period_weeks(): void
    {
        if (!$this->isAdmin()) {
            return;
        }

        $weeks = (int)$this->data->get('weeks');
        
        $settings = BotSettings::first() ?? new BotSettings();
        $settings->period_weeks = $weeks;
        $settings->save();

        $this->chat->message("Период сбора установлен на: $weeks " . $this->getWeekWord($weeks))
            ->send();

        $this->settings_reports();
    }

    //Управление тегами
    public function manage_hashtags(): void
    {
        if (!$this->isAdmin()) {
            return;
        }

        $settings = BotSettings::first() ?? new BotSettings();
        $hashtags = $settings->hashtags ?? [];

        $keyboard = Keyboard::make()
            ->row([Button::make('➕ Добавить хештег')->action('add_hashtag')])
            ->row([Button::make('❌ Удалить хештег')->action('remove_hashtag')]);

        if (!empty($hashtags)) {
            $message = "Текущие хештеги:\n\n";
            foreach ($hashtags as $tag => $title) {
                $message .= "$tag -> $title\n";
            }
        } else {
            $message = "Список хештегов пуст.";
        }

        $keyboard->row([Button::make('⬅️ Назад')->action('settings_reports')]);

        $this->chat
        ->edit($this->messageId)
        ->message($message)
            ->keyboard($keyboard)
            ->send();
    }


    //Добавить тег
    public function add_hashtag(): void
    {
        if (!$this->isAdmin()) {
            return;
        }
    
        $this->chat->message("Отправьте хештег и заголовок в формате:\n#хештег Заголовок отчета")
            ->send();
    }

    //Удалить тег
    public function remove_hashtag(): void
    {
        if (!$this->isAdmin()) {
            \Log::info('remove_hashtag: Пользователь не админ');
            return;
        }
    
        $settings = BotSettings::first();
        if (!$settings) {
            \Log::info('remove_hashtag: Настройки не найдены');
            $this->chat->message("Ошибка: настройки бота не найдены")->send();
            $this->manage_hashtags();
            return;
        }
    
        $hashtags = $settings->hashtags ?? [];
        \Log::info('remove_hashtag: Текущие хештеги', ['hashtags' => $hashtags]);
    
        if (empty($hashtags)) {
            \Log::info('remove_hashtag: Список хештегов пуст');
            $this->chat->message("Список хештегов пуст.")->send();
            $this->manage_hashtags();
            return;
        }
    
        $tag = $this->data->get('tag');
        if ($tag) {
            if (!array_key_exists($tag, $hashtags)) {
                \Log::info('remove_hashtag: Хештег не найден', ['tag' => $tag]);
                $this->chat->message("Хештег $tag не найден")->send();
                $this->remove_hashtag();
                return;
            }
    
            unset($hashtags[$tag]);
            $settings->hashtags = $hashtags;
            $settings->save();
    
            \Log::info('remove_hashtag: Хештег удален', ['tag' => $tag]);
            $this->chat->message("Хештег $tag успешно удален")->send();
    
            $this->manage_hashtags();
            return;
        }
    
        $keyboard = Keyboard::make();
        foreach ($hashtags as $tag => $title) {
            \Log::info('remove_hashtag: Создаю кнопку для тега', ['tag' => $tag]);
            $keyboard->row([
                Button::make($tag)->action('remove_hashtag')->param('tag', $tag)
            ]);
        }
    
        $keyboard->row([Button::make('⬅️ Назад')->action('manage_hashtags')]);
    
        \Log::info('remove_hashtag: Отправляю сообщение с клавиатурой');
        $this->chat
        ->edit($this->messageId)
        ->message("Выберите хештег для удаления:")
            ->keyboard($keyboard)
            ->send();
    }    

    //Ввод тега
    protected function handleHashtagInput(string $text): void
    {
        if (!preg_match('/^#([^\s]+)\s+(.+)$/u', $text, $matches)) {
            $this->chat->message("Ошибка: Неправильный формат. Введите в формате: #тег Описание")->send();
            return;
        }
    
        $hashtag = '#' . $matches[1];
        $title = trim($matches[2]);
    
        $settings = BotSettings::first() ?? new BotSettings();
        $hashtags = $settings->hashtags ?? [];
    
        $hashtags[$hashtag] = $title;
        $settings->hashtags = $hashtags;
        $settings->save();
    
        $this->chat->message("Хештег $hashtag добавлен с заголовком: $title")->send();
        $this->manage_hashtags();
    }    

    //Создать отчёт
    public function generate_report(): void
    {
        if (!$this->isAdmin()) {
            return;
        }
    
        $settings = BotSettings::first();
        $now = Carbon::now($this->timezone);
        $periodWeeks = $settings->period_weeks ?? 1;
        
        $startOfPeriod = $now->copy()
            ->subWeeks($periodWeeks);
        
        $endOfPeriod = $now->copy();
    
        \Artisan::call('reports:check', [
            '--start' => $startOfPeriod->toDateTimeString(),
            '--end' => $endOfPeriod->toDateTimeString(),
        ]);
    
        $this->chat->message('Отчет успешно сгенерирован')->send();
    }
}
