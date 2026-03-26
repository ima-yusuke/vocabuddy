<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Word;

class CheckWordLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        $currentPlan = $user->currentPlan();

        // プランに単語制限がない場合（null = 無制限）
        if ($currentPlan->word_limit === null) {
            return $next($request);
        }

        // 現在の単語数を取得
        $wordCount = Word::where('user_id', $user->id)->count();

        // 制限を超えている場合
        if ($wordCount >= $currentPlan->word_limit) {
            return redirect()->back()->with('error', "単語登録数の上限（{$currentPlan->word_limit}語）に達しています。プランをアップグレードしてください。");
        }

        return $next($request);
    }
}
