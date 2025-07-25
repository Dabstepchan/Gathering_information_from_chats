<?php

namespace App\Services\Telegram;

use App\Services\Telegram\Abstracts\MenuServiceInterface;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;

class MenuService implements MenuServiceInterface
{
    public function showMainMenu($chat): void
    {
        $keyboard = $this->createMainMenuKeyboard();
        
        $chat->message('Выберите действие:')
            ->keyboard($keyboard)
            ->send();
    }

    public function editToMainMenu($chat, int $messageId): void
    {
        $keyboard = $this->createMainMenuKeyboard();
        
        $chat->edit($messageId)
            ->message('Выберите действие:')
            ->keyboard($keyboard)
            ->send();
    }

    private function createMainMenuKeyboard(): Keyboard
    {
        return Keyboard::make()
            ->row([
                Button::make('📊 Отчет')->action('generateReport'),
                Button::make('⚙️ Настройки')->action('settings'),
            ])
            ->row([
                Button::make('ℹ️ Информация')->action('info'),
            ]);
    }
}