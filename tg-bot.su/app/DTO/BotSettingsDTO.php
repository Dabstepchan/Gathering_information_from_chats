<?php

namespace App\DTO;

class BotSettingsDTO
{
    public function __construct(
        public readonly string $reportDay,
        public readonly string $reportTime,
        public readonly int $periodWeeks,
        public readonly array $hashtags,
        public readonly ?string $awaitingInput = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            reportDay: $data['report_day'] ?? 'Понедельник',
            reportTime: $data['report_time'] ?? '10:00:00',
            periodWeeks: (int) ($data['period_weeks'] ?? 1),
            hashtags: $data['hashtags'] ?? [
                '#митрепорт' => 'Тут не было митрепортов',
                '#еженедельныйотчет' => 'Тут не было еж.отчетов',
            ],
            awaitingInput: $data['awaiting_input'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'report_day' => $this->reportDay,
            'report_time' => $this->reportTime,
            'period_weeks' => $this->periodWeeks,
            'hashtags' => $this->hashtags,
            'awaiting_input' => $this->awaitingInput,
        ];
    }
}