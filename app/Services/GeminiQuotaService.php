<?php

namespace App\Services;

use App\Models\AiUsageLog;
use Illuminate\Support\Carbon;

/**
 * Gemini無料枠（単語調べ autocomplete）の残量を ai_usage_logs から集計する。
 * クォータはAPIキー単位で全ユーザー・全モデル共有のため、modelやuser_idでは絞らずtypeのみで集計する。
 */
class GeminiQuotaService
{
    private const TYPE = 'autocomplete';

    public function status(): array
    {
        $rpm = (int) config('services.gemini.flash_rpm', 10);
        $rpd = (int) config('services.gemini.flash_rpd', 20);

        $now = Carbon::now();
        $minuteAgo = $now->copy()->subSeconds(60);

        $minuteCount = $this->baseQuery()
            ->where('created_at', '>=', $minuteAgo)
            ->count();

        $dayCount = $this->baseQuery()
            ->today()
            ->count();

        $minuteRemaining = max(0, $rpm - $minuteCount);
        $dayRemaining = max(0, $rpd - $dayCount);

        return [
            'minute' => [
                'limit' => $rpm,
                'used' => $minuteCount,
                'remaining' => $minuteRemaining,
                'recovers_in_seconds' => $minuteRemaining > 0
                    ? 0
                    : $this->minuteRecoverySeconds($now, $minuteAgo),
            ],
            'day' => [
                'limit' => $rpd,
                'used' => $dayCount,
                'remaining' => $dayRemaining,
                'recovers_at' => $dayRemaining > 0
                    ? null
                    : $now->copy()->addDay()->startOfDay()->toIso8601String(),
            ],
        ];
    }

    private function baseQuery()
    {
        return AiUsageLog::ofType(self::TYPE);
    }

    /**
     * 1分窓が埋まっているとき、最古ログの60秒後までの残り秒数。
     */
    private function minuteRecoverySeconds(Carbon $now, Carbon $minuteAgo): int
    {
        $oldest = $this->baseQuery()
            ->where('created_at', '>=', $minuteAgo)
            ->min('created_at');

        if (!$oldest) {
            return 0;
        }

        $recoversAt = Carbon::parse($oldest)->addSeconds(60);
        return (int) max(0, round($now->diffInSeconds($recoversAt, false)));
    }
}
