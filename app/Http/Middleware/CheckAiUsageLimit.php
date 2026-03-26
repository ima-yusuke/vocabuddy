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

        // 日次制限のチェック
        if ($currentPlan->ai_reply_daily_limit !== null) {
            $todayUsage = AiUsageLog::where('user_id', $user->id)
                ->ofType($type)
                ->today()
                ->count();

            if ($todayUsage >= $currentPlan->ai_reply_daily_limit) {
                return redirect()->back()->with('error', "AI返信の1日の上限（{$currentPlan->ai_reply_daily_limit}回）に達しています。明日またはプランのアップグレードをご検討ください。");
            }
        }

        // 月次制限のチェック
        if ($currentPlan->ai_reply_monthly_limit !== null) {
            $monthlyUsage = AiUsageLog::where('user_id', $user->id)
                ->ofType($type)
                ->thisMonth()
                ->count();

            if ($monthlyUsage >= $currentPlan->ai_reply_monthly_limit) {
                return redirect()->back()->with('error', "AI返信の月間上限（{$currentPlan->ai_reply_monthly_limit}回）に達しています。来月またはプランのアップグレードをご検討ください。");
            }
        }

        return $next($request);
    }
}
