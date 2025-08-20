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
        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            //khóa ngoại với teams
            $table->foreignId('team_id')
            ->constrained('teams')
            ->onDelete('cascade');//xóa team thì xóa luôn thành viên trong team
            //khóa ngoại với users
            $table->foreignId('user_id')
            ->constrained('users')
            ->onDelete('cascade');//xóa user thì xóa luôn record này

            $table->enum('roleInTeam', ['product_owner','scrum_master', 'developer'])
            ->default('developer');
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_members');
    }
};
