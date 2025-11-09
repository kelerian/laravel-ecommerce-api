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
        Schema::table('user_group_user', function (Blueprint $table) {
            $table->dropColumn('id');
            $table->primary(['user_id', 'group_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_group_user', function (Blueprint $table) {
            $table->id()->first();
            $table->dropPrimary(['user_id', 'group_id']);
        });
    }
};
