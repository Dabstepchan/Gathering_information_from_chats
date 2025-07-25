<?php

namespace App\Enums;

enum WeekDay: string
{
    case MONDAY = 'monday';
    case TUESDAY = 'tuesday';
    case WEDNESDAY = 'wednesday';
    case THURSDAY = 'thursday';
    case FRIDAY = 'friday';
    case SATURDAY = 'saturday';
    case SUNDAY = 'sunday';

    public function label(): string
    {
        return __('days.' . $this->value);
    }

    public function englishName(): string
    {
        return match($this) {
            self::MONDAY => 'Monday',
            self::TUESDAY => 'Tuesday',
            self::WEDNESDAY => 'Wednesday',
            self::THURSDAY => 'Thursday',
            self::FRIDAY => 'Friday',
            self::SATURDAY => 'Saturday',
            self::SUNDAY => 'Sunday',
        };
    }

    public static function fromRussian(string $russianDay): ?self
    {
        return match($russianDay) {
            'Понедельник' => self::MONDAY,
            'Вторник' => self::TUESDAY,
            'Среда' => self::WEDNESDAY,
            'Четверг' => self::THURSDAY,
            'Пятница' => self::FRIDAY,
            'Суббота' => self::SATURDAY,
            'Воскресенье' => self::SUNDAY,
            default => null,
        };
    }

    public static function fromEnglish(string $englishDay): ?self
    {
        return match($englishDay) {
            'Monday' => self::MONDAY,
            'Tuesday' => self::TUESDAY,
            'Wednesday' => self::WEDNESDAY,
            'Thursday' => self::THURSDAY,
            'Friday' => self::FRIDAY,
            'Saturday' => self::SATURDAY,
            'Sunday' => self::SUNDAY,
            default => null,
        };
    }

    public static function all(): array
    {
        return [
            self::MONDAY,
            self::TUESDAY,
            self::WEDNESDAY,
            self::THURSDAY,
            self::FRIDAY,
            self::SATURDAY,
            self::SUNDAY,
        ];
    }
}