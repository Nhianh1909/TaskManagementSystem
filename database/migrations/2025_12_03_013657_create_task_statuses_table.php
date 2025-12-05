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
        Schema::create('task_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Tên hiển thị (To Do, In Progress...)
            $table->integer('order_index')->default(0); // Để sắp xếp
            $table->boolean('is_done')->default(false); // Đánh dấu là Done (để vẽ Burndown)
            $table->string('color_class')->default('border-gray-300'); // Màu sắc

            // team_id: null = dùng chung. Nếu muốn riêng từng team thì gán ID vào.
            $table->foreignId('team_id')->nullable()->constrained()->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_statuses');
    }
};
