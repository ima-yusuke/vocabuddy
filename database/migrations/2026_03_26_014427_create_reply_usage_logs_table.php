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
        Schema::create('reply_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('reply_templates')->onDelete('cascade');
            $table->timestamp('used_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reply_usage_logs');
    }
};
