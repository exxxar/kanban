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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade'); // Жесткая связь с карточкой

            // CRM поля из ТЗ
            $table->string('source')->nullable()->comment('Источник лида');
            $table->string('phone')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('company_name')->nullable();
            $table->text('address')->nullable();
            $table->json('links')->nullable(); // Массив ссылок

            // Этапы взаимодействия.
            // Примечание: в Kanban этапами обычно являются сами Колонки.
            // Но если нужно фиксировать статус отдельно (например, "Отказ"), добавим поле.
            $table->string('interaction_stage')->nullable();

            $table->text('deal_comment')->nullable()->comment('Комментарий к сделке');
            $table->string('partner')->nullable()->comment('Партнер по рефералке');

            $table->decimal('cost', 10, 2)->nullable()->comment('Стоимость размещения');
            $table->string('placement_type')->nullable()->comment('Вид размещения/пакет');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
