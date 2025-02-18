<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBotSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('bot_settings', function (Blueprint $table) {
            $table->id();
            $table->string('report_day')->default('Понедельник');
            $table->time('report_time')->default('10:00:00');
            $table->integer('period_weeks')->default(1);
            $table->json('hashtags')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bot_settings');
    }
}