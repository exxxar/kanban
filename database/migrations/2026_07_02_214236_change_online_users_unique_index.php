<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('online_users', function (Blueprint $table) {
            // Удаляем старый уникальный индекс
            $table->dropUnique(['session_id']);

            // Создаём составной уникальный индекс
            $table->unique(['session_id', 'board_uuid'], 'online_session_board_unique');
        });
    }

    public function down(): void
    {
        Schema::table('online_users', function (Blueprint $table) {
            $table->dropUnique('online_session_board_unique');
            $table->unique('session_id');
        });
    }
};
