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
            //Thêm cột epic_id để liên kết User Story với epics
            $table->foreignId('epic_id')
                  ->nullable() //User story có thể không thuộc epic nào
                  ->constrained('epics')
                  ->onDelete('set null')//Nếu epic bị xóa thì giữ lại User Story
                  ->after('sprint_id');//thêm cột epic_id sau cột sprint_id

            //Thêm cột parent_id để lien kết với các sub-task với User Story
            $table->foreignId('parent_id')
                    ->nullable()//sub-task có thể không có parent
                    ->constrained('tasks')
                    ->onDelete('cascade')//Nếu parent bị xóa thì xóa luôn sub-task
                    ->after('epic_id');//thêm cột parent_id sau cột epic_id
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Xóa theo thứ tự ngược lại
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');

            $table->dropForeign(['epic_id']);
            $table->dropColumn('epic_id');
        });
    }
};
