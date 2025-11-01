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
        Schema::create('retrospective_items', function (Blueprint $table) {
            $table->id();
            //liên kết với bảng retrospectives
            $table->foreignId('retrospective_id')
                    ->constrained('retrospectives')
                    ->onDelete('cascade');
            //liên kết với người viết tag
            $table->foreignId('user_id')
                    ->constrained('users')
                    ->onDelete('cascade');

            $table->string('content');
            $table->enum('type', ['bad', 'good', 'action']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retrospective_items');
    }
};
