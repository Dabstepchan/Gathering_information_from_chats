<?php

namespace App\Services\Telegram\Abstracts;

interface SettingsCommandServiceInterface
{
    public function showSettings($chat, int $messageId): void;
    public function showReportSettings($chat, int $messageId): void;
    public function showDaySelector($chat, int $messageId): void;
    public function saveReportDay(string $day, $chat): void;
    public function showTimeSelector($chat, int $messageId): void;
    public function saveReportTime(string $hour, $chat): void;
    public function showPeriodSelector($chat, int $messageId): void;
    public function savePeriodWeeks(string $weeks, $chat): void;
}