<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Mục đích: Cho phép start_date và end_date có thể NULL
     * Lý do: Future Sprints (status='planning') chưa có ngày bắt đầu/kết thúc
     */
    public function up(): void
    {
        Schema::table('sprints', function (Blueprint $table) {
            // Sửa start_date và end_date thành nullable
            $table->dateTime('start_date')->nullable()->change();
            $table->dateTime('end_date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     * Rollback: Đưa về NOT NULL như cũ
     */
    public function down(): void
    {
        Schema::table('sprints', function (Blueprint $table) {
            // Khôi phục về NOT NULL
            $table->dateTime('start_date')->nullable(false)->change();
            $table->dateTime('end_date')->nullable(false)->change();
        });
    }
};
