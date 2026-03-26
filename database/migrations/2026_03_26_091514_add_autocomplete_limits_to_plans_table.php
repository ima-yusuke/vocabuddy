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
        Schema::table('plans', function (Blueprint $table) {
            $table->integer('ai_autocomplete_daily_limit')->nullable()->after('ai_reply_monthly_limit');
            $table->integer('ai_autocomplete_monthly_limit')->nullable()->after('ai_autocomplete_daily_limit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['ai_autocomplete_daily_limit', 'ai_autocomplete_monthly_limit']);
        });
    }
};
