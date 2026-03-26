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
                'name' => '無料プラン',
                'limit' => 20,
                'monthly' => 0,
                'yearly' => 0,
                'features' => ['20単語まで登録', 'AI自動補完', '単語テスト', '返信アシスタント']
            ],
            [
                'type' => 'standard',
                'name' => 'スタンダード',
                'limit' => 50,
                'monthly' => 500,
                'yearly' => 5000,
                'features' => ['50単語まで登録', 'AI自動補完', '単語テスト', '返信アシスタント']
            ],
            [
                'type' => 'plus',
                'name' => 'プラス',
                'limit' => 200,
                'monthly' => 1000,
                'yearly' => 10000,
                'features' => ['200単語まで登録', 'AI自動補完', '単語テスト', '返信アシスタント']
            ],
            [
                'type' => 'premium',
                'name' => 'プレミアム',
                'limit' => null,
                'monthly' => 2000,
                'yearly' => 20000,
                'features' => ['無制限で登録', 'AI自動補完', '単語テスト', '返信アシスタント', '優先サポート']
            ],
        ];

        return view('landing', compact('plans'));
    }
}
