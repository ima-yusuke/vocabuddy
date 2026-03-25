# フェーズ1: ランディングページ + ルーティング変更 実装計画

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** 新規ユーザー向けのランディングページを作成し、既存の単語一覧を /words に移動する

**Architecture:**
- `/` にランディングページ（LandingController）を配置
- 既存の単語一覧を `/words` に移動し、認証必須に変更
- mont.jpスタイルのミニマルデザインで実装

**Tech Stack:** Laravel 11, Blade Templates, Tailwind CSS

---

## 実装タスクリスト

### A. 準備・確認

1. - [ ] 現在のブランチとgitの状態を確認
2. - [ ] 設計書（`docs/plans/2026-03-25-monetization-landing-page-design.md`）を読んで全体像を把握

### B. コントローラー作成

3. - [ ] `LandingController` を作成
4. - [ ] `PricingController` を作成

### C. ビューファイルの準備

5. - [ ] 既存の `resources/views/index.blade.php` を `resources/views/words/index.blade.php` に移動
6. - [ ] `resources/views/landing.blade.php` を作成（ヒーローセクション）
7. - [ ] `landing.blade.php` に機能紹介セクションを追加
8. - [ ] `landing.blade.php` に無料訴求セクションを追加
9. - [ ] `landing.blade.php` にプラン価格表セクションを追加
10. - [ ] `landing.blade.php` に使い方セクションを追加
11. - [ ] `landing.blade.php` にフッターを追加
12. - [ ] `resources/views/pricing.blade.php` を作成

### D. ルーティング変更

13. - [ ] `routes/web.php` のバックアップを取る（コメントとして残す）
14. - [ ] `/` のルートを `LandingController@index` に変更
15. - [ ] `/words` ルートグループを作成（認証ミドルウェア付き）
16. - [ ] 既存の単語関連ルートを `/words` 配下に移動
17. - [ ] `/pricing` ルートを追加

### E. 既存コントローラーの修正

18. - [ ] `MainController@ShowIndex` のビューパスを `index` から `words.index` に変更
19. - [ ] `MainController` の他のメソッドでビュー名を確認・修正

### F. ナビゲーション・ヘッダーの更新

20. - [ ] `resources/views/components/navigation.blade.php` または `header.blade.php` を確認
21. - [ ] ナビゲーションにログイン状態の分岐を追加（未ログイン時・ログイン時）
22. - [ ] 未ログイン時: ホーム、機能、価格、ログイン、新規登録
23. - [ ] ログイン時: ロゴ、単語帳、テスト、返信アシスタント、ログアウト

### G. リダイレクト設定の確認

24. - [ ] `app/Http/Middleware/Authenticate.php` のリダイレクト先を確認
25. - [ ] `app/Http/Controllers/Auth/RegisteredUserController.php` の登録後リダイレクトを `/words` に変更
26. - [ ] `app/Http/Controllers/Auth/AuthenticatedSessionController.php` のログイン後リダイレクトを `/words` に変更

### H. 動作確認

27. - [ ] ローカルサーバー起動: `php artisan serve`
28. - [ ] `/` にアクセスしてランディングページが表示されることを確認
29. - [ ] 未ログインで `/words` にアクセスしてログインページにリダイレクトされることを確認
30. - [ ] 新規ユーザー登録して `/words` にリダイレクトされることを確認
31. - [ ] ログイン後に `/words` で単語一覧が表示されることを確認
32. - [ ] `/pricing` にアクセスして価格ページが表示されることを確認
33. - [ ] ナビゲーションメニューがログイン状態で切り替わることを確認
34. - [ ] モバイル表示を確認（Chrome DevTools）

### I. デザイン調整

35. - [ ] ランディングページの余白・スペーシングを調整
36. - [ ] カラーパレットの一貫性を確認
37. - [ ] タイポグラフィのサイズと行間を調整
38. - [ ] レスポンシブ対応を確認（タブレット・スマホ）

### J. 最終確認とコミット

39. - [ ] すべての既存機能（単語登録、編集、削除、テスト、返信アシスタント）が正常動作することを確認
40. - [ ] キャッシュクリア: `php artisan config:clear && php artisan route:clear && php artisan view:clear`
41. - [ ] 変更ファイルを確認: `git status`
42. - [ ] 変更をステージング: `git add .`
43. - [ ] コミット: `git commit -m "feat: Add landing page and update routing for Phase 1"`

---

## 詳細実装ガイド

### タスク3: LandingController を作成

**ファイル:** `app/Http/Controllers/LandingController.php`

**実装内容:**

```php
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
```

---

### タスク4: PricingController を作成

**ファイル:** `app/Http/Controllers/PricingController.php`

**実装内容:**

```php
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

        return view('pricing', compact('plans'));
    }
}
```

---

### タスク5: 既存ビューファイルの移動

**コマンド:**

```bash
# resources/views/words ディレクトリを作成
mkdir -p resources/views/words

# index.blade.php を words ディレクトリに移動
mv resources/views/index.blade.php resources/views/words/index.blade.php
```

**確認:**
```bash
ls resources/views/words/
# 出力: index.blade.php
```

---

### タスク6-11: landing.blade.php を作成

**ファイル:** `resources/views/landing.blade.php`

**実装内容:**

```blade
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>VocaBuddy - AI搭載の英語学習アプリ</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-gray-900">
    <!-- ナビゲーション -->
    <nav class="border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="/" class="text-2xl font-bold text-gray-900">VocaBuddy</a>
                </div>
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#features" class="text-gray-600 hover:text-gray-900 transition">機能</a>
                    <a href="#pricing" class="text-gray-600 hover:text-gray-900 transition">価格</a>
                    @guest
                        <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900 transition">ログイン</a>
                        <a href="{{ route('register') }}" class="bg-gray-900 text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition">新規登録</a>
                    @else
                        <a href="{{ route('words.index') }}" class="text-gray-600 hover:text-gray-900 transition">単語帳</a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-600 hover:text-gray-900 transition">ログアウト</button>
                        </form>
                    @endguest
                </div>
            </div>
        </div>
    </nav>

    <!-- ヒーローセクション -->
    <section class="py-20 md:py-32">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl md:text-6xl font-bold text-gray-900 mb-6 leading-tight">
                    映画や日常で出会った英単語を、<br>
                    自分だけの単語帳に
                </h1>
                <p class="text-xl md:text-2xl text-gray-600 mb-12 leading-relaxed">
                    AIが自動補完。返信文も生成。<br>
                    20単語まで無料で使える英語学習アプリ
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('register') }}" class="bg-gray-900 text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-gray-800 transition transform hover:scale-105">
                        無料で始める
                    </a>
                    <a href="#features" class="bg-white text-gray-900 border-2 border-gray-900 px-8 py-4 rounded-lg text-lg font-semibold hover:bg-gray-50 transition">
                        機能を見る
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- 主要機能セクション -->
    <section id="features" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl md:text-4xl font-bold text-center text-gray-900 mb-16">
                主要機能
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- 機能1 -->
                <div class="bg-white p-8 rounded-2xl border border-gray-200 hover:shadow-lg transition">
                    <div class="text-4xl mb-4">📚</div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">自分だけの単語帳</h3>
                    <p class="text-gray-600 leading-relaxed">映画や日常で学んだ英単語を登録して、自分専用の単語帳を作成できます。</p>
                </div>
                <!-- 機能2 -->
                <div class="bg-white p-8 rounded-2xl border border-gray-200 hover:shadow-lg transition">
                    <div class="text-4xl mb-4">🤖</div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">AI自動補完</h3>
                    <p class="text-gray-600 leading-relaxed">英単語を入力するだけで、AIが発音・品詞・意味を自動取得します。</p>
                </div>
                <!-- 機能3 -->
                <div class="bg-white p-8 rounded-2xl border border-gray-200 hover:shadow-lg transition">
                    <div class="text-4xl mb-4">✏️</div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">単語テスト</h3>
                    <p class="text-gray-600 leading-relaxed">登録した単語から4択クイズを自動生成。楽しくテストで定着できます。</p>
                </div>
                <!-- 機能4 -->
                <div class="bg-white p-8 rounded-2xl border border-gray-200 hover:shadow-lg transition">
                    <div class="text-4xl mb-4">💬</div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">AI返信アシスタント</h3>
                    <p class="text-gray-600 leading-relaxed">登録した単語を使った自然な英語の返信文をAIが生成します。</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 無料訴求セクション -->
    <section class="py-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">
                まずは無料で試せる
            </h2>
            <p class="text-xl text-gray-600 mb-8 leading-relaxed">
                20単語まで無料で登録可能。<br>
                クレジットカード不要で今すぐ始められます。
            </p>
            <a href="{{ route('register') }}" class="inline-block bg-gray-900 text-white px-10 py-4 rounded-lg text-lg font-semibold hover:bg-gray-800 transition transform hover:scale-105">
                無料で始める
            </a>
        </div>
    </section>

    <!-- プラン・価格表セクション -->
    <section id="pricing" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl md:text-4xl font-bold text-center text-gray-900 mb-16">
                プラン・料金
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($plans as $plan)
                <div class="bg-white p-8 rounded-2xl border-2 {{ $plan['type'] === 'standard' ? 'border-gray-900' : 'border-gray-200' }} hover:shadow-lg transition">
                    @if($plan['type'] === 'standard')
                    <div class="text-xs font-semibold text-gray-900 mb-2">おすすめ</div>
                    @endif
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ $plan['name'] }}</h3>
                    <div class="mb-4">
                        <span class="text-4xl font-bold text-gray-900">¥{{ number_format($plan['monthly']) }}</span>
                        <span class="text-gray-600">/月</span>
                    </div>
                    @if($plan['yearly'] > 0)
                    <div class="text-sm text-gray-600 mb-6">年額 ¥{{ number_format($plan['yearly']) }}</div>
                    @endif
                    <div class="mb-6">
                        <div class="text-lg font-semibold text-gray-900 mb-2">
                            @if($plan['limit'])
                                {{ $plan['limit'] }}単語まで
                            @else
                                無制限
                            @endif
                        </div>
                    </div>
                    <ul class="space-y-3 mb-8">
                        @foreach($plan['features'] as $feature)
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-gray-900 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-600">{{ $feature }}</span>
                        </li>
                        @endforeach
                    </ul>
                    @if($plan['type'] === 'free')
                    <a href="{{ route('register') }}" class="block w-full text-center bg-gray-900 text-white px-6 py-3 rounded-lg font-semibold hover:bg-gray-800 transition">
                        無料で始める
                    </a>
                    @else
                    <button class="block w-full text-center bg-gray-200 text-gray-500 px-6 py-3 rounded-lg font-semibold cursor-not-allowed">
                        準備中
                    </button>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- 使い方セクション -->
    <section class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl md:text-4xl font-bold text-center text-gray-900 mb-16">
                使い方
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                <!-- ステップ1 -->
                <div class="text-center">
                    <div class="w-16 h-16 bg-gray-900 text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-6">1</div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">無料で新規登録</h3>
                    <p class="text-gray-600 leading-relaxed">メールアドレスとパスワードだけで、すぐに始められます。</p>
                </div>
                <!-- ステップ2 -->
                <div class="text-center">
                    <div class="w-16 h-16 bg-gray-900 text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-6">2</div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">単語を登録</h3>
                    <p class="text-gray-600 leading-relaxed">英単語を入力すると、AIが自動で発音・意味を補完します。</p>
                </div>
                <!-- ステップ3 -->
                <div class="text-center">
                    <div class="w-16 h-16 bg-gray-900 text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-6">3</div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">テスト・返信で活用</h3>
                    <p class="text-gray-600 leading-relaxed">単語テストで定着を確認したり、返信アシスタントで実践的に使えます。</p>
                </div>
            </div>
        </div>
    </section>

    <!-- フッター -->
    <footer class="bg-gray-50 border-t border-gray-200 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center text-gray-600">
                <div class="mb-4">
                    <a href="#" class="hover:text-gray-900 transition mx-3">プライバシーポリシー</a>
                    <a href="#" class="hover:text-gray-900 transition mx-3">利用規約</a>
                    <a href="#" class="hover:text-gray-900 transition mx-3">お問い合わせ</a>
                </div>
                <p>&copy; 2026 VocaBuddy. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
```

---

### タスク12: pricing.blade.php を作成

**ファイル:** `resources/views/pricing.blade.php`

**実装内容:**

```blade
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>料金プラン - VocaBuddy</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-gray-900">
    <!-- ナビゲーション -->
    <nav class="border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="/" class="text-2xl font-bold text-gray-900">VocaBuddy</a>
                </div>
                <div class="hidden md:flex items-center space-x-8">
                    <a href="/#features" class="text-gray-600 hover:text-gray-900 transition">機能</a>
                    <a href="/pricing" class="text-gray-900 font-semibold">価格</a>
                    @guest
                        <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900 transition">ログイン</a>
                        <a href="{{ route('register') }}" class="bg-gray-900 text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition">新規登録</a>
                    @else
                        <a href="{{ route('words.index') }}" class="text-gray-600 hover:text-gray-900 transition">単語帳</a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-600 hover:text-gray-900 transition">ログアウト</button>
                        </form>
                    @endguest
                </div>
            </div>
        </div>
    </nav>

    <!-- ヘッダー -->
    <section class="py-16 text-center">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">料金プラン</h1>
            <p class="text-xl text-gray-600">あなたに最適なプランを選んでください</p>
        </div>
    </section>

    <!-- プラン比較表 -->
    <section class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($plans as $plan)
                <div class="bg-white p-8 rounded-2xl border-2 {{ $plan['type'] === 'standard' ? 'border-gray-900 shadow-lg' : 'border-gray-200' }}">
                    @if($plan['type'] === 'standard')
                    <div class="text-xs font-semibold text-gray-900 mb-2">おすすめ</div>
                    @endif
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ $plan['name'] }}</h3>
                    <div class="mb-4">
                        <span class="text-4xl font-bold text-gray-900">¥{{ number_format($plan['monthly']) }}</span>
                        <span class="text-gray-600">/月</span>
                    </div>
                    @if($plan['yearly'] > 0)
                    <div class="text-sm text-gray-600 mb-6">
                        年額 ¥{{ number_format($plan['yearly']) }}
                        <span class="text-xs text-green-600">({{ round((1 - $plan['yearly'] / ($plan['monthly'] * 12)) * 100) }}%お得)</span>
                    </div>
                    @endif
                    <div class="mb-6">
                        <div class="text-lg font-semibold text-gray-900 mb-2">
                            @if($plan['limit'])
                                {{ $plan['limit'] }}単語まで
                            @else
                                無制限
                            @endif
                        </div>
                    </div>
                    <ul class="space-y-3 mb-8">
                        @foreach($plan['features'] as $feature)
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-gray-900 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-600">{{ $feature }}</span>
                        </li>
                        @endforeach
                    </ul>
                    @if($plan['type'] === 'free')
                    <a href="{{ route('register') }}" class="block w-full text-center bg-gray-900 text-white px-6 py-3 rounded-lg font-semibold hover:bg-gray-800 transition">
                        無料で始める
                    </a>
                    @else
                    <button class="block w-full text-center bg-gray-200 text-gray-500 px-6 py-3 rounded-lg font-semibold cursor-not-allowed">
                        準備中
                    </button>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-center text-gray-900 mb-12">よくある質問</h2>
            <div class="space-y-6">
                <div class="bg-white p-6 rounded-lg border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">無料プランでも全機能使えますか？</h3>
                    <p class="text-gray-600">はい、20単語までの登録制限がありますが、AI自動補完、単語テスト、返信アシスタントなど全機能をご利用いただけます。</p>
                </div>
                <div class="bg-white p-6 rounded-lg border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">プランはいつでも変更できますか？</h3>
                    <p class="text-gray-600">はい、いつでもアップグレード・ダウングレードが可能です。（フェーズ3で実装予定）</p>
                </div>
                <div class="bg-white p-6 rounded-lg border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">年額プランの方がお得ですか？</h3>
                    <p class="text-gray-600">はい、年額プランは月額プランと比べて約17%お得になります。</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">
                今すぐ始めよう
            </h2>
            <p class="text-xl text-gray-600 mb-8">
                20単語まで無料。クレジットカード不要。
            </p>
            <a href="{{ route('register') }}" class="inline-block bg-gray-900 text-white px-10 py-4 rounded-lg text-lg font-semibold hover:bg-gray-800 transition transform hover:scale-105">
                無料で始める
            </a>
        </div>
    </section>

    <!-- フッター -->
    <footer class="bg-gray-50 border-t border-gray-200 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center text-gray-600">
                <div class="mb-4">
                    <a href="#" class="hover:text-gray-900 transition mx-3">プライバシーポリシー</a>
                    <a href="#" class="hover:text-gray-900 transition mx-3">利用規約</a>
                    <a href="#" class="hover:text-gray-900 transition mx-3">お問い合わせ</a>
                </div>
                <p>&copy; 2026 VocaBuddy. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
```

---

### タスク14-17: routes/web.php のルーティング変更

**ファイル:** `routes/web.php`

**変更内容:**

```php
<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MainController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\ReplyController;
use App\Http\Controllers\WordAutoCompleteController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\PricingController;

// ランディングページ（全ユーザー）
Route::get('/', [LandingController::class, 'index'])->name('landing');

// プラン・価格ページ（全ユーザー）
Route::get('/pricing', [PricingController::class, 'index'])->name('pricing');

// 単語一覧・管理（認証必須）
Route::middleware(['auth'])->group(function () {
    Route::get('/words', [MainController::class, 'ShowIndex'])->name('words.index');
    Route::post('/words', [MainController::class, 'AddWord'])->name('words.store');
    Route::get('/words/edit/{id}', [MainController::class, 'EditWord'])->name('words.edit');
    Route::patch('/words/update/{id}', [MainController::class, 'UpdateWord'])->name('words.update');
    Route::delete('/words', [MainController::class, 'DeleteWord'])->name('words.destroy');

    // 単語テスト
    Route::get('/test', [TestController::class, 'ShowTestStart'])->name('ShowTest');
    Route::get('/test/start', [TestController::class, 'StartTest'])->name('StartTest');
    Route::get('/test/question', [TestController::class, 'ShowQuestion'])->name('ShowQuestion');
    Route::post('/test/check', [TestController::class, 'CheckAnswer'])->name('CheckAnswer');

    // 返信アシスタント
    Route::get('/reply-assistant', [ReplyController::class, 'ShowReplyAssistant'])->name('ShowReplyAssistant');
    Route::post('/reply-assistant/generate', [ReplyController::class, 'GenerateReply'])->name('GenerateReply');

    // AI自動補完
    Route::post('/word/autocomplete', [WordAutoCompleteController::class, 'autocomplete'])->name('AutocompleteWord');
});

// ダッシュボード（認証必須）
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// プロフィール管理（認証必須）
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
```

---

### タスク18-19: MainController のビューパス変更

**ファイル:** `app/Http/Controllers/MainController.php`

**変更箇所:**

```php
// ShowIndex メソッド
public function ShowIndex(Request $request)
{
    $search = $request->input('search');

    if ($search) {
        // 検索処理
        $words = Word::with('japanese')
            ->where('word', 'like', '%' . $search . '%')
            ->orWhere('en_example', 'like', '%' . $search . '%')
            ->orWhere('jp_example', 'like', '%' . $search . '%')
            ->orWhereHas('japanese', function($query) use ($search) {
                $query->where('japanese', 'like', '%' . $search . '%');
            })
            ->get();

        $totalCount = Word::count();
    } else {
        $words = Word::with('japanese')->get();
        $totalCount = $words->count();
    }

    // ビュー名を 'index' から 'words.index' に変更
    return view("words.index", compact('words', 'totalCount'));
}

// EditWord メソッド
public function EditWord($id)
{
    $word = Word::with('japanese')->findOrFail($id);
    return view('edit-word', compact('word'));
}
```

**確認:** 他のメソッド（EditWord）がビューを正しく参照しているか確認

---

### タスク25-26: 認証後のリダイレクト設定

**ファイル1:** `app/Http/Controllers/Auth/RegisteredUserController.php`

**変更箇所:**

```php
public function store(Request $request): RedirectResponse
{
    $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
    ]);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
    ]);

    event(new Registered($user));

    Auth::login($user);

    // リダイレクト先を '/words' に変更
    return redirect()->route('words.index');
}
```

**ファイル2:** `app/Providers/RouteServiceProvider.php` （Laravel 11では不要な場合あり）

もし `RouteServiceProvider` が存在する場合:

```php
public const HOME = '/words';
```

---

### タスク27-34: 動作確認チェックリスト

**ローカルサーバー起動:**
```bash
php artisan serve
```

**確認項目:**

1. `http://localhost:8000/` にアクセス
   - ランディングページが表示される
   - ヒーロー、機能、価格、使い方セクションがすべて表示される

2. 未ログイン状態で `http://localhost:8000/words` にアクセス
   - `/login` にリダイレクトされる

3. 新規ユーザー登録
   - `/register` から登録
   - 登録後に `/words` にリダイレクトされる

4. ログイン後に `/words` にアクセス
   - 単語一覧が表示される
   - 既存の機能（単語登録、編集、削除）が動作する

5. `/pricing` にアクセス
   - 価格ページが表示される

6. ナビゲーションメニュー
   - 未ログイン時: ホーム、機能、価格、ログイン、新規登録
   - ログイン時: 単語帳、テスト、返信アシスタント、ログアウト

7. モバイル表示確認
   - Chrome DevTools でレスポンシブ確認

---

### タスク35-38: デザイン調整

**確認項目:**

- 各セクション間に十分な余白（py-16〜py-24）があるか
- テキストの可読性（フォントサイズ、行間）
- カラーの一貫性（グレー系 + アクセントカラー）
- ホバー効果が適切に動作するか
- モバイル・タブレット・デスクトップで適切に表示されるか

**調整が必要な場合:**
- `landing.blade.php` のTailwindクラスを調整
- スペーシング、フォントサイズ、カラーを微調整

---

### タスク39-43: 最終確認とコミット

**動作確認:**
```bash
# 単語登録
# 単語編集
# 単語削除
# 単語テスト
# 返信アシスタント
```

すべて正常に動作することを確認。

**キャッシュクリア:**
```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

**変更確認:**
```bash
git status
```

**ステージング:**
```bash
git add .
```

**コミット:**
```bash
git commit -m "feat: Add landing page and update routing for Phase 1

- Create LandingController and PricingController
- Add landing.blade.php with hero, features, pricing, and usage sections
- Add pricing.blade.php with detailed plan comparison
- Move existing index.blade.php to words/index.blade.php
- Update routes: / to landing, /words for authenticated word list
- Update MainController to use words.index view
- Update auth redirects to /words
- Add navigation with login state switching
- Implement mont.jp inspired minimal design
"
```

---

## 完了条件

以下がすべて満たされたらフェーズ1完了:

- [ ] `/` でランディングページが表示される
- [ ] `/pricing` で価格ページが表示される
- [ ] 未ログインで `/words` にアクセスするとログインページにリダイレクトされる
- [ ] 新規登録後に `/words` にリダイレクトされる
- [ ] ログイン後に `/words` で単語一覧が表示される
- [ ] 既存のすべての機能（単語登録、編集、削除、テスト、返信アシスタント）が正常動作する
- [ ] モバイル・タブレット・デスクトップで適切に表示される
- [ ] デザインがmont.jpスタイル（ミニマル、余白、シンプルな配色）になっている
- [ ] すべての変更がgitにコミットされている

---

## 次のステップ

フェーズ1完了後、フェーズ2（プラン管理機能）の実装計画を作成します。
