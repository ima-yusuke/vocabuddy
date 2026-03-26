<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index()
    {
        // プラン情報を配列で定義（フェーズ2以降はDBから取得）
        $plans = [
            [
                'type' => 'free',
                'name' => 'Free',
                'limit' => 50,
                'ai_daily' => 2,
                'ai_monthly' => null,
                'monthly' => 0,
                'yearly' => 0,
                'features' => ['50単語まで登録', 'AI返信 2回/日', 'AI自動補完 5回/日', '単語テスト', '返信アシスタント']
            ],
            [
                'type' => 'basic',
                'name' => 'Basic',
                'limit' => 300,
                'ai_daily' => 10,
                'ai_monthly' => null,
                'monthly' => 400,
                'yearly' => 4000,
                'features' => ['300単語まで登録', 'AI返信 10回/日', 'AI自動補完 20回/日', '単語テスト', '返信アシスタント']
            ],
            [
                'type' => 'pro',
                'name' => 'Pro',
                'limit' => null,
                'ai_daily' => null,
                'ai_monthly' => 300,
                'monthly' => 1000,
                'yearly' => 10000,
                'features' => ['無制限で登録', 'AI返信 月300回', 'AI自動補完 月200回', '単語テスト', '返信アシスタント', '高性能AIモデル']
            ],
            [
                'type' => 'premium',
                'name' => 'Premium',
                'limit' => null,
                'ai_daily' => null,
                'ai_monthly' => null,
                'monthly' => 1750,
                'yearly' => 17500,
                'features' => ['無制限で登録', 'AI返信 無制限', 'AI自動補完 無制限', '単語テスト', '返信アシスタント', '高性能AIモデル', '優先サポート']
            ],
        ];

        return view('landing', compact('plans'));
    }
}
