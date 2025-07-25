<?php

namespace App\DTO;

use Carbon\Carbon;

class ReportPeriodDTO
{
    public function __construct(
        public readonly Carbon $startDate,
        public readonly Carbon $endDate,
        public readonly string $timezone = 'Asia/Novokuznetsk'
    ) {}

    public function getSheetTitle(): string
    {
        return $this->startDate->format('d.m.y H:i') . ' - ' . $this->endDate->format('d.m.y H:i');
    }
}