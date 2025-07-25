<?php

namespace App\Services\Chat\Abstracts;

interface ChatNameFormatterServiceInterface
{
    public function formatChatName(?string $chatName): string;
}
