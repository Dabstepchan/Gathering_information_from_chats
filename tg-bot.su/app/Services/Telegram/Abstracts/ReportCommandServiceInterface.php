<?php

namespace App\Services\Telegram\Abstracts;

interface ReportCommandServiceInterface
{
    public function showReportMenu($chat, int $messageId): void;
    public function generateReport($chat): void;
}