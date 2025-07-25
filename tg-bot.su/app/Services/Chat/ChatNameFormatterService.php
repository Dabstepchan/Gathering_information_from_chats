<?php

namespace App\Services\Chat;

use App\Services\Chat\Abstracts\ChatNameFormatterServiceInterface;

class ChatNameFormatterService implements ChatNameFormatterServiceInterface
{
    private array $prefixesToRemove = [
        '[group]',
        '[supergroup]',
        '[channel]',
        '[private]',
    ];

    public function formatChatName(?string $chatName): string
    {
        if (empty($chatName)) {
            return 'Неизвестный чат';
        }

        $formattedName = trim($chatName);

        foreach ($this->prefixesToRemove as $prefix) {
            if (str_starts_with($formattedName, $prefix)) {
                $formattedName = trim(substr($formattedName, strlen($prefix)));
                break;
            }
        }

        if (empty($formattedName)) {
            return 'Неизвестный чат';
        }

        return $formattedName;
    }
}
