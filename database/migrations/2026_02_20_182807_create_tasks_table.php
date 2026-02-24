<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('column_id')->constrained()->cascadeOnDelete();

            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('priority', ['low', 'medium', 'high'])->default('low');
            $table->tinyInteger('type')->default(0);
            $table->date('due_date')->nullable();
            $table->json('labels')->nullable();
            $table->json('data')->nullable();
            $table->json('subtasks')->nullable();
            $table->json('attachments')->nullable();
            $table->dateTime('last_viewed_at')->nullable();
            $table->integer('position')->default(0);
            $table->foreignId('board_id')->nullable();

            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
