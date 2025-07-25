<?php

namespace App\Services\Telegram;

use App\Repositories\Telegram\Abstracts\TelegraphMessageRepositoryInterface;
use App\Services\Bot\Abstracts\BotSettingsServiceInterface;
use App\Services\Telegram\Abstracts\HashtagCommandServiceInterface;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;

class HashtagCommandService implements HashtagCommandServiceInterface
{
    public function __construct(
        private readonly BotSettingsServiceInterface $botSettingsService,
        private readonly TelegraphMessageRepositoryInterface $messageRepository
    ) {}

    public function processChatMessage(string $messageText, $chat): void
    {
        if ($this->isHashtagInput($messageText)) {
            $this->handleHashtagInput($messageText, $chat);
            return;
        }

        if ($this->containsHashtag($messageText)) {
            $this->handleMessage(
                $messageText,
                $chat->id,
                now()->setTimezone(config('telegram.timezone'))
            );
        }
    }

    public function handleMessage(string $message, int $chatId, $sentAt): void
    {
        $this->messageRepository->createMessage([
            'telegraph_chat_id' => $chatId,
            'message' => $message,
            'sent_at' => $sentAt,
        ]);
    }

    /**
     * Проверяет, является ли сообщение добавлением нового хештега (формат: #тег описание)
     */
    public function isHashtagInput(string $text): bool
    {
        return preg_match('/^#([^\s]+)\s+(.+)$/u', $text) === 1;
    }

    /**
     * Проверяет, содержит ли сообщение хештеги
     */
    public function containsHashtag(string $text): bool
    {
        return preg_match('/#[^\s]+/u', $text) === 1;
    }

    public function handleHashtagInput(string $text, $chat): void
    {
        if (!preg_match('/^#([^\s]+)\s+(.+)$/u', $text, $matches)) {
            $chat->message("Ошибка: Неправильный формат. Введите в формате: #тег Описание")->send();
            return;
        }

        $hashtag = '#' . $matches[1];
        $title = trim($matches[2]);

        $settings = $this->botSettingsService->getBotSettings();
        $hashtags = $settings ? $settings->hashtags : [];

        $hashtags[$hashtag] = $title;
        $this->botSettingsService->updateBotSettings(['hashtags' => $hashtags]);

        $chat->message("Хештег $hashtag добавлен с заголовком: $title")->send();
    }

    public function showHashtagManagement($chat, int $messageId): void
    {
        $settings = $this->botSettingsService->getBotSettings();
        $hashtags = $settings ? $settings->hashtags : [];

        $keyboard = Keyboard::make()
            ->row([Button::make('➕ Добавить хештег')->action('add_hashtag')])
            ->row([Button::make('❌ Удалить хештег')->action('remove_hashtag')]);

        $message = empty($hashtags) ? "Список хештегов пуст." : $this->formatHashtagsList($hashtags);

        $keyboard->row([Button::make('⬅️ Назад')->action('settings_reports')]);

        $chat->edit($messageId)
            ->message($message)
            ->keyboard($keyboard)
            ->send();
    }

    public function requestHashtagInput($chat): void
    {
        $chat->message("Отправьте хештег и заголовок в формате:\n#хештег Заголовок отчета")->send();
    }

    public function removeHashtag(string $tag, $chat): void
    {
        $settings = $this->botSettingsService->getBotSettings();
        $hashtags = $settings ? $settings->hashtags : [];

        if (!array_key_exists($tag, $hashtags)) {
            $chat->message("Хештег $tag не найден")->send();
            return;
        }

        unset($hashtags[$tag]);
        $this->botSettingsService->updateBotSettings(['hashtags' => $hashtags]);
        $chat->message("Хештег $tag успешно удален")->send();
    }

    public function showHashtagRemovalSelector($chat, int $messageId): void
    {
        $settings = $this->botSettingsService->getBotSettings();
        $hashtags = $settings ? $settings->hashtags : [];

        if (empty($hashtags)) {
            $chat->message("Список хештегов пуст.")->send();
            return;
        }

        $keyboard = Keyboard::make();
        foreach ($hashtags as $tag => $title) {
            $keyboard->row([
                Button::make($tag)->action('remove_hashtag')->param('tag', $tag)
            ]);
        }

        $keyboard->row([Button::make('⬅️ Назад')->action('manage_hashtags')]);

        $chat->edit($messageId)
            ->message("Выберите хештег для удаления:")
            ->keyboard($keyboard)
            ->send();
    }

    private function formatHashtagsList(array $hashtags): string
    {
        $message = "Текущие хештеги:\n\n";
        foreach ($hashtags as $tag => $title) {
            $message .= "$tag -> $title\n";
        }
        return $message;
    }
}
