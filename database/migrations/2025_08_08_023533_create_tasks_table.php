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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sprint_id')
            ->nullable()//task có thể không thuộc sprint nào
            ->constrained('sprints')
            ->onDelete('cascade');//khi xóa sprint thì xóa task
            $table->foreignId('created_by')
            ->constrained('users')
            ->onDelete('cascade');//Ai là người tạo task
            $table->foreignId('assigned_to')
            ->nullable()
            ->constrained('users')
            ->onDelete('set null');//Ai là người được gán task
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->integer('storyPoints')->nullable();//điểm công việc
            $table->enum('status', ['toDo', 'inProgress', 'done'])->default('toDo');
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
