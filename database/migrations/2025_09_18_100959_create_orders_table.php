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

        Schema::create('order_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('title')->unique();
            $table->string('slug')->unique();;
        });

        Schema::create('pay_types', function (Blueprint $table) {
            $table->id();
            $table->string('title')->unique();;
            $table->string('slug')->unique();;
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('order_status_id')
                ->constrained('order_statuses')
                ->restrictOnDelete();
            $table->decimal('final_price');
            $table->string('email');
            $table->string('phone');
            $table->string('address');
            $table->foreignid('pay_type_id')
                ->constrained('pay_types')
                ->restrictOnDelete();


            $table->index('created_at');
            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'order_status_id']);
        });

    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
        Schema::dropIfExists('pay_types');
        Schema::dropIfExists('order_statuses');

    }
};
