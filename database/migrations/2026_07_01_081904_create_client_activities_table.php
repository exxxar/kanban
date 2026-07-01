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
        Schema::create('client_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('action_type'); // created, updated, stage_changed, message, comment, deal_closed, etc.
            $table->string('title'); // Краткое описание
            $table->text('description')->nullable(); // Детали
            $table->json('metadata')->nullable(); // Дополнительные данные (старое/новое значение)
            $table->string('user_name')->nullable(); // Кто совершил действие (пока без авторизации)
            $table->timestamps();

            $table->index(['client_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_activities');
    }
};
