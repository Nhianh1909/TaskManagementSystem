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
        Schema::create('retrospectives', function (Blueprint $table) {
            $table->id();
            //liên kết với bảng sprints đã hoàn thành
            $table->foreignId('sprint_id')
                  ->constrained('sprints')
                  ->onDelete('cascade');//khi xóa sprint thì xóa retrospective
            //liên kết với team thực hiện sprint
            $table->foreignId('team_id')
                  ->constrained('teams')
                  ->onDelete('cascade');//khi xóa team thì xóa retrospective
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retrospectives');
    }
};
