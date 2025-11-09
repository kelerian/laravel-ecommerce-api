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
        Schema::table('plus_users', function (Blueprint $table) {
            $table->dropColumn('gender');
            $table->foreignId('gender_id')
                ->nullable()
                ->constrained('genders')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plus_users', function (Blueprint $table) {
            $table->dropForeign(['gender_id']);

            $table->dropColumn('gender_id');

            $table->string('gender')->nullable();
        });
    }
};
