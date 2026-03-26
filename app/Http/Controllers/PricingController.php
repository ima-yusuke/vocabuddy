<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PricingController extends Controller
{
    public function index()
    {
        // LandingControllerと同じプラン情報
        $plans = [
            [
                'type' => 'free',
                'name' => 'Free',
                'limit' => 50,
                'ai_daily' => 2,
                'ai_monthly' => null,
                'monthly' => 0,
                'yearly' => 0,
                'features' => ['50単語まで登録', 'AI返信 2回/日', 'AI自動補完', '単語テスト', '返信アシスタント']
            ],
            [
                'type' => 'basic',
                'name' => 'Basic',
                'limit' => 300,
                'ai_daily' => 10,
                'ai_monthly' => null,
                'monthly' => 400,
                'yearly' => 4000,
                'features' => ['300単語まで登録', 'AI返信 10回/日', 'AI自動補完', '単語テスト', '返信アシスタント']
            ],
            [
                'type' => 'pro',
                'name' => 'Pro',
                'limit' => null,
                'ai_daily' => null,
                'ai_monthly' => 300,
                'monthly' => 1000,
                'yearly' => 10000,
                'features' => ['無制限で登録', 'AI返信 月300回', 'AI自動補完', '単語テスト', '返信アシスタント', '高性能AIモデル']
            ],
            [
                'type' => 'premium',
                'name' => 'Premium',
                'limit' => null,
                'ai_daily' => null,
                'ai_monthly' => null,
                'monthly' => 1750,
                'yearly' => 17500,
                'features' => ['無制限で登録', 'AI返信 無制限', 'AI自動補完', '単語テスト', '返信アシスタント', '高性能AIモデル', '優先サポート']
            ],
        ];

        return view('pricing', compact('plans'));
    }
}
