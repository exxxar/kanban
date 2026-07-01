<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('online_users', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 64)->unique(); // Уникальный идентификатор сессии
            $table->string('board_uuid', 36)->index(); // UUID доски
            $table->string('ip_address', 45);
            $table->string('user_agent', 500)->nullable();
            $table->string('device_type', 20)->nullable(); // desktop/mobile/tablet
            $table->string('browser', 50)->nullable();
            $table->string('os', 50)->nullable();
            $table->string('screen_resolution', 20)->nullable(); // 1920x1080
            $table->string('canvas_hash', 64)->nullable(); // Fingerprint
            $table->timestamp('last_seen_at')->index();
            $table->timestamps();

            // Индекс для быстрой очистки старых записей
            $table->index(['board_uuid', 'last_seen_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('online_users');
    }
};
