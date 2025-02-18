<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('telegraph_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('telegraph_chat_id')->constrained('telegraph_chats')->cascadeOnDelete();
            $table->text('message');
            $table->timestamp('sent_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegraph_messages');
    }
};
