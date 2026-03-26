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
        Schema::create('reply_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('category', ['friend', 'romantic', 'work', 'family'])->nullable();
            $table->text('partner_message'); // 相手の英文
            $table->text('intent_ja'); // 自分の意図・日本語
            $table->text('reply_en'); // 生成された英文
            $table->text('reply_ja')->nullable(); // 日本語訳
            $table->json('vocab_ids')->nullable(); // 使用単語ID
            $table->integer('times_used')->default(0); // 使用回数
            $table->json('embedding')->nullable(); // Gemini Embeddings用
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reply_templates');
    }
};
