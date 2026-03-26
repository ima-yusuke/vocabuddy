<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\AiUsageLog;

class CheckAiUsageLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $type = 'reply'): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        $currentPlan = $user->currentPlan();

        // タイプに応じて制限カラムとエラーメッセージを決定
        $limitConfig = $this->getLimitConfig($type);

        // 日次制限のチェック
        $dailyLimitColumn = $limitConfig['daily_column'];
        if ($currentPlan->$dailyLimitColumn !== null) {
            $todayUsage = AiUsageLog::where('user_id', $user->id)
                ->ofType($type)
                ->today()
                ->count();

            if ($todayUsage >= $currentPlan->$dailyLimitColumn) {
                $featureName = $limitConfig['feature_name'];
                return response()->json([
                    'success' => false,
                    'error' => "{$featureName}の1日の上限（{$currentPlan->$dailyLimitColumn}回）に達しています。明日またはプランのアップグレードをご検討ください。"
                ], 429);
            }
        }

        // 月次制限のチェック
        $monthlyLimitColumn = $limitConfig['monthly_column'];
        if ($currentPlan->$monthlyLimitColumn !== null) {
            $monthlyUsage = AiUsageLog::where('user_id', $user->id)
                ->ofType($type)
                ->thisMonth()
                ->count();

            if ($monthlyUsage >= $currentPlan->$monthlyLimitColumn) {
                $featureName = $limitConfig['feature_name'];
                return response()->json([
                    'success' => false,
                    'error' => "{$featureName}の月間上限（{$currentPlan->$monthlyLimitColumn}回）に達しています。来月またはプランのアップグレードをご検討ください。"
                ], 429);
            }
        }

        return $next($request);
    }

    /**
     * タイプに応じた制限設定を取得
     */
    private function getLimitConfig(string $type): array
    {
        return match($type) {
            'autocomplete' => [
                'daily_column' => 'ai_autocomplete_daily_limit',
                'monthly_column' => 'ai_autocomplete_monthly_limit',
                'feature_name' => 'AI単語自動補完',
            ],
            'reply' => [
                'daily_column' => 'ai_reply_daily_limit',
                'monthly_column' => 'ai_reply_monthly_limit',
                'feature_name' => 'AI返信',
            ],
            default => [
                'daily_column' => 'ai_reply_daily_limit',
                'monthly_column' => 'ai_reply_monthly_limit',
                'feature_name' => 'AI機能',
            ],
        };
    }
}
