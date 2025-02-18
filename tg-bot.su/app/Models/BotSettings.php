<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotSettings extends Model
{
    protected $fillable = [
        'report_day',
        'report_time',
        'period_weeks',
        'hashtags',
        'awaiting_input'
    ];

    protected $casts = [
        'hashtags' => 'array'
    ];

    protected $attributes = [
        'hashtags' => '{"#митрепорт":"","#еженедельныйотчет":""}',
        'report_day' => 'Понедельник',
        'report_time' => '10:00',
        'period_weeks' => 1
    ];
}