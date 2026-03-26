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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Free, Basic, Pro, Premium
            $table->string('slug')->unique(); // free, basic, pro, premium
            $table->integer('word_limit')->nullable(); // 50, 300, null, null
            $table->integer('ai_reply_daily_limit')->nullable(); // 2, 10, null, null
            $table->integer('ai_reply_monthly_limit')->nullable(); // null, null, 300, null
            $table->integer('price_monthly')->default(0); // 0, 500, 1200, 2000
            $table->integer('price_yearly')->default(0); // 0, 5000, 12000, 20000
            $table->string('ai_model')->default('flash'); // flash, flash, pro, pro
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
