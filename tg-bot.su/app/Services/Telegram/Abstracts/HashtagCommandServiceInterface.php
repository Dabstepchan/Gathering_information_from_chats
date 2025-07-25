<?php

namespace App\Services\Telegram\Abstracts;

interface HashtagCommandServiceInterface
{
    public function processChatMessage(string $messageText, $chat): void;
    public function handleMessage(string $message, int $chatId, $sentAt): void;
    public function isHashtagInput(string $text): bool;
    public function containsHashtag(string $text): bool;
    public function handleHashtagInput(string $text, $chat): void;
    public function showHashtagManagement($chat, int $messageId): void;
    public function requestHashtagInput($chat): void;
    public function removeHashtag(string $tag, $chat): void;
    public function showHashtagRemovalSelector($chat, int $messageId): void;
}
