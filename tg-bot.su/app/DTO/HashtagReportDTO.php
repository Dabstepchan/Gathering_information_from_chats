<?php

namespace App\DTO;

class HashtagReportDTO
{
    public function __construct(
        public readonly string $hashtag,
        public readonly string $reportTitle,
        public readonly array $missingChats = []
    ) {}

    public function hasIssues(): bool
    {
        return !empty($this->missingChats);
    }

    public function getFormattedReport(): string
    {
        if (!$this->hasIssues()) {
            return '';
        }

        return "**$this->reportTitle**\n" . implode("\n", $this->missingChats);
    }
}
