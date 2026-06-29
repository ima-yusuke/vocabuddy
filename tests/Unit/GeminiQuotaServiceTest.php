<?php

namespace Tests\Unit;

use App\Models\AiUsageLog;
use App\Models\User;
use App\Services\GeminiQuotaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class GeminiQuotaServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.gemini.flash_rpm' => 10]);
        config(['services.gemini.flash_rpd' => 20]);
        Carbon::setTestNow('2026-06-25 12:00:00');
        $this->user = User::factory()->create();
    }

    private function logAt(string $time): void
    {
        AiUsageLog::create([
            'user_id' => $this->user->id,
            'type' => 'autocomplete',
            'tokens_used' => null,
            'model_used' => 'gemini-2.5-flash',
            'created_at' => $time,
        ]);
    }

    public function test_full_quota_when_no_usage(): void
    {
        $q = (new GeminiQuotaService())->status();

        $this->assertSame(10, $q['minute']['remaining']);
        $this->assertSame(10, $q['minute']['limit']);
        $this->assertSame(20, $q['day']['remaining']);
        $this->assertSame(20, $q['day']['limit']);
        $this->assertSame(0, $q['minute']['recovers_in_seconds']);
    }

    public function test_counts_only_last_minute_for_minute_window(): void
    {
        $this->logAt('2026-06-25 11:59:30'); // 30秒前 → カウント
        $this->logAt('2026-06-25 11:58:30'); // 90秒前 → 対象外
        $this->logAt('2026-06-25 12:00:00'); // 今 → カウント

        $q = (new GeminiQuotaService())->status();

        $this->assertSame(8, $q['minute']['remaining']); // 10 - 2
        $this->assertSame(17, $q['day']['remaining']);    // 20 - 3（全部今日）
    }

    public function test_other_types_ignored_but_all_autocomplete_models_counted(): void
    {
        // 別タイプ（reply）はカウントされない
        AiUsageLog::create([
            'user_id' => $this->user->id,
            'type' => 'reply',
            'tokens_used' => null,
            'model_used' => 'gemini-3-flash-preview',
            'created_at' => '2026-06-25 12:00:00',
        ]);

        // Proユーザーのautocomplete（別モデル名）も同じキーのクォータを消費 → カウントする
        AiUsageLog::create([
            'user_id' => $this->user->id,
            'type' => 'autocomplete',
            'tokens_used' => null,
            'model_used' => 'gemini-2.0-flash-exp',
            'created_at' => '2026-06-25 12:00:00',
        ]);

        $q = (new GeminiQuotaService())->status();

        $this->assertSame(9, $q['minute']['remaining']); // 10 - 1（autocomplete1件、replyは無視）
        $this->assertSame(19, $q['day']['remaining']);    // 20 - 1
    }

    public function test_minute_recovery_seconds_when_exhausted(): void
    {
        // 直近1分に10件（=上限）。最古は11:59:20
        for ($i = 0; $i < 10; $i++) {
            $this->logAt('2026-06-25 11:59:20');
        }

        $q = (new GeminiQuotaService())->status();

        $this->assertSame(0, $q['minute']['remaining']);
        // 最古11:59:20 + 60s = 12:00:20。今が12:00:00 → 20秒後
        $this->assertSame(20, $q['minute']['recovers_in_seconds']);
    }

    public function test_remaining_never_negative(): void
    {
        for ($i = 0; $i < 25; $i++) {
            $this->logAt('2026-06-25 09:00:00'); // 今日だが1分窓外
        }

        $q = (new GeminiQuotaService())->status();

        $this->assertSame(0, $q['day']['remaining']); // 20 - 25 をクランプ
    }

    public function test_minute_recovery_zero_at_exact_60s_boundary(): void
    {
        // 上限ちょうど。最古が60秒前ぴったり = 11:59:00 → 回復まで0秒
        for ($i = 0; $i < 10; $i++) {
            $this->logAt('2026-06-25 11:59:00');
        }

        $q = (new GeminiQuotaService())->status();

        $this->assertSame(0, $q['minute']['remaining']);
        $this->assertSame(0, $q['minute']['recovers_in_seconds']);
    }

    public function test_day_recovers_at_is_next_midnight_when_exhausted(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $this->logAt('2026-06-25 10:00:00');
        }

        $q = (new GeminiQuotaService())->status();

        $this->assertSame(0, $q['day']['remaining']);
        $this->assertSame(
            \Illuminate\Support\Carbon::parse('2026-06-26 00:00:00')->toIso8601String(),
            $q['day']['recovers_at']
        );
    }

    public function test_day_recovers_at_is_null_when_quota_remains(): void
    {
        $this->logAt('2026-06-25 10:00:00');

        $q = (new GeminiQuotaService())->status();

        $this->assertNull($q['day']['recovers_at']);
    }
}
