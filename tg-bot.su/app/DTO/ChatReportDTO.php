<?php

namespace App\DTO;

class ChatReportDTO
{
    public function __construct(
        public readonly int $chatId,
        public readonly string $chatName,
        public readonly string $chatLink,
        public readonly array $missingHashtags = []
    ) {}

    public function getFormattedLink(): string
    {
        if ($this->chatLink === "-") {
            return $this->chatName;
        }

        return "[$this->chatName]($this->chatLink)";
    }
}
