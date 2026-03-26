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
        Schema::create('weak_words', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('word_id')->constrained()->onDelete('cascade');
            $table->integer('incorrect_count')->default(0); // 累計不正解数
            $table->integer('consecutive_correct_count')->default(0); // 連続正解数
            $table->timestamp('last_incorrect_at')->nullable(); // 最後に間違えた日時
            $table->timestamps();

            // ユーザーと単語の組み合わせはユニーク
            $table->unique(['user_id', 'word_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weak_words');
    }
};
