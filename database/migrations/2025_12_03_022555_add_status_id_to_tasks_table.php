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
        Schema::table('tasks', function (Blueprint $table) {
            // Thêm cột status_id, cho phép null (để không bị lỗi với dữ liệu cũ)
            // Đặt nó nằm sau cột 'priority' cho dễ nhìn
            $table->foreignId('status_id')
                  ->nullable()
                  ->after('priority')
                  ->constrained('task_statuses')
                  ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Xóa khóa ngoại và xóa cột khi rollback
            $table->dropForeign(['status_id']);
            $table->dropColumn('status_id');
        });
    }
};
