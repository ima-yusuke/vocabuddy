# VocaBuddy マネタイズ機能・ランディングページ設計書

## 概要

VocaBuddyに課金プラン機能とランディングページを追加し、マネタイズを実現する。無料ユーザーには20単語までの登録制限を設け、有料プランでより多くの単語を登録できるようにする。

**設計日**: 2026-03-25
**対象フェーズ**: フェーズ1（ランディングページ）、フェーズ2（プラン管理）、フェーズ3（Stripe連携）

---

## 目次

1. [アーキテクチャ概要](#1-アーキテクチャ概要)
2. [データベース設計](#2-データベース設計)
3. [ルーティング設計](#3-ルーティング設計)
4. [ランディングページのUI/UX設計](#4-ランディングページのuiux設計)
5. [認証とプラン管理のロジック](#5-認証とプラン管理のロジック)
6. [コンポーネント構成](#6-コンポーネント構成)
7. [エラーハンドリングとバリデーション](#7-エラーハンドリングとバリデーション)
8. [セキュリティとプライバシー](#8-セキュリティとプライバシー)
9. [フェーズ別実装詳細](#9-フェーズ別実装詳細)
10. [テスト戦略とデプロイメント](#10-テスト戦略とデプロイメント)

---

## 1. アーキテクチャ概要

### 基本方針

3フェーズに分けて段階的に実装し、各フェーズで動作確認を行いながら進める。

### フェーズ1: ランディングページ + ルーティング変更

**目的**: 新規ユーザーに訴求するランディングページを公開

**実装内容**:
- `/` に新しいランディングページを配置
- 既存の単語一覧を `/words` に移動
- 認証システムの活用（Laravel Breezeは既に導入済み）
- プラン情報を静的に表示（価格表示のみ）
- 「無料で始める」ボタンから新規登録へ誘導

### フェーズ2: プラン管理機能

**目的**: ユーザーごとの単語登録数制限を実装

**実装内容**:
- `subscriptions` テーブル追加（ユーザーID、プランタイプ、制限数）
- `plans` テーブル追加（プランマスターデータ）
- `words` テーブルに `user_id` カラム追加
- ユーザー登録時にデフォルトで無料プラン（20単語制限）を割り当て
- 単語登録時に制限チェックのミドルウェア
- プラン変更画面（管理者が手動で変更可能）

### フェーズ3: Stripe連携

**目的**: 自動課金システムの実装

**実装内容**:
- Laravel Cashier (Stripe) 導入
- サブスクリプション購入フロー
- Webhookでプラン更新
- 決済履歴・領収書ページ

### 技術スタック

- **バックエンド**: Laravel 11（既存）
- **フロントエンド**: Blade Templates + Tailwind CSS
- **データベース**: SQLite（開発環境）、MySQL/PostgreSQL（本番推奨）
- **認証**: Laravel Breeze（既存）
- **決済**: Stripe（フェーズ3で追加）

---

## 2. データベース設計

### 既存テーブル（変更なし）

- **`users`**: ユーザー情報
- **`words`**: 単語情報（word, part_of_speech, pronunciation_katakana, en_example, jp_example）
- **`japanese`**: 単語の意味（複数の意味に対応）

### 新規テーブル（フェーズ2で追加）

#### `subscriptions` テーブル

ユーザーのサブスクリプション情報を管理

```php
Schema::create('subscriptions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('plan_type'); // 'free', 'standard', 'plus', 'premium'
    $table->integer('word_limit')->nullable(); // 20, 50, 200, null(unlimited)
    $table->timestamp('started_at');
    $table->timestamp('expires_at')->nullable(); // サブスク期限（フェーズ3で使用）
    $table->string('stripe_subscription_id')->nullable(); // フェーズ3で使用
    $table->string('status')->default('active'); // active, canceled, expired
    $table->timestamps();
});
```

#### `plans` テーブル（マスターデータ）

プラン定義のマスターテーブル

```php
Schema::create('plans', function (Blueprint $table) {
    $table->id();
    $table->string('plan_type')->unique(); // 'free', 'standard', 'plus', 'premium'
    $table->string('name'); // '無料プラン', 'スタンダード', 'プラス', 'プレミアム'
    $table->integer('word_limit')->nullable(); // 20, 50, 200, null
    $table->integer('monthly_price')->default(0); // 月額料金（円）
    $table->integer('yearly_price')->default(0); // 年額料金（円）
    $table->text('features')->nullable(); // JSON形式で機能リスト
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

**初期データ例**:
```php
[
    ['plan_type' => 'free', 'name' => '無料プラン', 'word_limit' => 20, 'monthly_price' => 0, 'yearly_price' => 0],
    ['plan_type' => 'standard', 'name' => 'スタンダード', 'word_limit' => 50, 'monthly_price' => 500, 'yearly_price' => 5000],
    ['plan_type' => 'plus', 'name' => 'プラス', 'word_limit' => 200, 'monthly_price' => 1000, 'yearly_price' => 10000],
    ['plan_type' => 'premium', 'name' => 'プレミアム', 'word_limit' => null, 'monthly_price' => 2000, 'yearly_price' => 20000],
]
```

### 既存テーブルの変更（フェーズ2）

#### `words` テーブルに `user_id` 追加

```php
Schema::table('words', function (Blueprint $table) {
    $table->foreignId('user_id')->nullable()->after('id')->constrained()->onDelete('cascade');
});
```

### リレーション

- **User** hasOne **Subscription**
- **Subscription** belongsTo **User**
- **Subscription** belongsTo **Plan** (plan_type経由)
- **User** hasMany **Words**
- **Word** belongsTo **User**

---

## 3. ルーティング設計

### フェーズ1のルーティング

#### 新規ルート

```php
// ランディングページ（全ユーザー）
Route::get('/', [LandingController::class, 'index'])->name('landing');

// プラン・価格ページ（全ユーザー）
Route::get('/pricing', [PricingController::class, 'index'])->name('pricing');

// 単語一覧（認証必須）
Route::middleware(['auth'])->group(function () {
    Route::get('/words', [MainController::class, 'ShowIndex'])->name('words.index');
    Route::post('/words', [MainController::class, 'AddWord'])->name('words.store');
    Route::get('/words/edit/{id}', [MainController::class, 'EditWord'])->name('words.edit');
    Route::patch('/words/update/{id}', [MainController::class, 'UpdateWord'])->name('words.update');
    Route::delete('/words', [MainController::class, 'DeleteWord'])->name('words.destroy');
});
```

#### 既存ルートは維持

- `/test`, `/reply-assistant`, `/dashboard`, `/profile` など

### フェーズ2で追加するルート

```php
// プラン管理（認証必須）
Route::middleware(['auth'])->group(function () {
    Route::get('/subscription', [SubscriptionController::class, 'show'])->name('subscription.show');
    Route::get('/subscription/change', [SubscriptionController::class, 'change'])->name('subscription.change');
});
```

### フェーズ3で追加するルート

```php
// Stripe決済
Route::middleware(['auth'])->group(function () {
    Route::post('/subscription/checkout', [SubscriptionController::class, 'checkout'])->name('subscription.checkout');
    Route::get('/subscription/success', [SubscriptionController::class, 'success'])->name('subscription.success');
    Route::get('/subscription/cancel', [SubscriptionController::class, 'cancelCheckout'])->name('subscription.cancel');
});

// Stripe Webhook（認証不要）
Route::post('/webhook/stripe', [WebhookController::class, 'handleStripe']);
```

### リダイレクト設定

- 未ログインユーザーが `/words` にアクセス → `/login` へリダイレクト
- ログイン成功後 → `/words` へリダイレクト
- 新規登録成功後 → `/words` へリダイレクト（無料プラン自動付与）

---

## 4. ランディングページのUI/UX設計

### デザインコンセプト

**参考サイト**: [株式会社モンブラン](https://mont.jp/) のデザインテイストを取り入れる

- **カラー**: モノクロベース（白・黒・グレー）+ アクセントカラー1色のシンプルな配色
- **スタイル**: ミニマルで余白を活かした余裕のあるレイアウト。遊び心のあるイラストや装飾要素を適度に配置
- **レスポンシブ**: モバイルファーストで設計
- **余白**: 白空間を十分に確保し、情報を詰め込みすぎない
- **装飾要素**: シンプルなイラストやアイコン、キャラクター的な装飾で親しみやすさを演出
- **セクション構成**: 段階的に情報を展開し、各セクションの区切りを明確に

### ランディングページの構成セクション

#### 1. ヒーローセクション（ファーストビュー）

- **キャッチコピー**: 「映画や日常で出会った英単語を、自分だけの単語帳に」
  - 大きな見出しで中央寄せまたは左寄せ
  - 十分な余白を確保
- **サブコピー**: 「AIが自動補完。返信文も生成。20単語まで無料で使える英語学習アプリ」
  - キャッチコピーより小さく、読みやすいサイズ
- **CTA**: 「無料で始める」ボタン（大きく目立つ）
  - シンプルな角丸ボタン
  - アクセントカラーで目立たせる
- **ビジュアル**:
  - シンプルなイラストや装飾要素（単語帳、AI、学習のイメージ）
  - 遊び心のあるキャラクターやアイコンを配置
  - 白空間を十分に確保し、すっきりとした印象に

#### 2. 主要機能セクション（3-4カラム）

グリッドレイアウトで各機能を紹介。各カードには：
- シンプルなアイコンまたはイラスト
- 機能名（見出し）
- 簡潔な説明文
- 十分な余白で読みやすく

**機能内容**:
- 📚 **自分だけの単語帳**: 映画や日常で学んだ単語を登録
- 🤖 **AI自動補完**: 発音・品詞・意味を自動取得
- ✏️ **単語テスト**: 楽しくテストで定着
- 💬 **AI返信アシスタント**: 登録した単語を使った返信文を生成

**デザイン**:
- 背景は白またはごく薄いグレー
- カード間の余白を十分に確保
- ホバー時に軽くシャドウや変化を加える

#### 3. 無料で始められる強調セクション

- 「まずは無料で試せる」
- 20単語まで無料
- クレジットカード不要

#### 4. プラン・価格表セクション

**レイアウト**:
- 4つのプラン（Free/Standard/Plus/Premium）をカード形式で横並び（モバイルは縦積み）
- 各カード間に十分な余白
- 推奨プランを視覚的に強調（大きくする、バッジをつけるなど）

**カードデザイン**:
- シンプルな白背景 + 細い枠線
- プラン名、価格、単語数制限、主な特徴を階層的に配置
- 月額・年額の切り替えタブ
- 「準備中」または「無料で始める」ボタン（フェーズ1では決済機能なし）

**装飾**:
- 各プランに合ったシンプルなアイコンやイラスト
- アクセントカラーを控えめに使用

**プラン例**:
| プラン | 単語数 | 月額 | 年額 |
|--------|-------|------|------|
| 無料 | 20単語 | ¥0 | ¥0 |
| スタンダード | 50単語 | ¥500 | ¥5,000 |
| プラス | 200単語 | ¥1,000 | ¥10,000 |
| プレミアム | 無制限 | ¥2,000 | ¥20,000 |

※金額と単語数は後から調整可能

#### 5. 使い方セクション（3ステップ）

1. 無料で新規登録
2. 単語を登録（AIが自動補完）
3. テストや返信アシスタントで活用

#### 6. フッター

**デザイン**:
- シンプルで控えめ
- 背景色: 薄いグレーまたは白
- 余白を十分に確保

**コンテンツ**:
- プライバシーポリシー、利用規約へのリンク
- お問い合わせ
- コピーライト
- SNSリンク（将来的に）

### デザイン実装ガイドライン

#### カラーパレット（例）

- **背景**: #FFFFFF（白）、#F9FAFB（ごく薄いグレー）
- **テキスト**: #111827（濃いグレー/黒）、#6B7280（中間グレー）
- **メインカラー**: 既存のprimary色を踏襲（またはブルー系）
- **アクセントカラー**: 既存のaccent色を踏襲（または暖色系）
- **ボーダー**: #E5E7EB（薄いグレー）

※既存のTailwind設定に合わせて調整

#### タイポグラフィ

- **見出し（H1）**: 大きく（text-4xl〜text-6xl）、太字（font-bold）
- **見出し（H2-H3）**: 中サイズ（text-2xl〜text-3xl）、セミボールド（font-semibold）
- **本文**: 読みやすいサイズ（text-base〜text-lg）
- **行間**: ゆったりと（leading-relaxed）

#### スペーシング

- **セクション間**: 大きな余白（py-16〜py-24）
- **要素間**: 適度な余白（mb-8〜mb-12）
- **コンテナ幅**: 最大幅を制限（max-w-6xl〜max-w-7xl）

#### イラスト・装飾

- **スタイル**: シンプルでフラットなイラスト
- **配置**: コンテンツを邪魔しない程度に配置
- **カラー**: モノクロ + アクセントカラーで統一感を持たせる

### ナビゲーション

#### 未ログイン時

- ロゴ、機能、価格、ログイン、新規登録

#### ログイン時

- ロゴ、単語帳、テスト、返信アシスタント、プラン、ログアウト

---

## 5. 認証とプラン管理のロジック

### ユーザー登録フロー（フェーズ1）

#### 新規登録時の処理

```php
// RegisteredUserController.php
public function store(Request $request)
{
    // ユーザー作成
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
    ]);

    // 無料プランを自動付与（フェーズ2で実装）
    // Subscription::create([
    //     'user_id' => $user->id,
    //     'plan_type' => 'free',
    //     'word_limit' => 20,
    //     'started_at' => now(),
    //     'status' => 'active',
    // ]);

    Auth::login($user);
    return redirect()->route('words.index'); // /words へ
}
```

### 単語登録制限チェック（フェーズ2）

#### Middleware: CheckWordLimit

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Word;

class CheckWordLimit
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        $subscription = $user->subscription;

        // サブスクリプションがない場合はエラー
        if (!$subscription) {
            return redirect()->route('words.index')
                ->with('error', 'プラン情報が見つかりません。');
        }

        // 無制限プランはチェック不要
        if (is_null($subscription->word_limit)) {
            return $next($request);
        }

        // 現在の単語数を取得
        $currentWordCount = Word::where('user_id', $user->id)->count();

        // 制限チェック
        if ($currentWordCount >= $subscription->word_limit) {
            return redirect()->route('words.index')
                ->with('error', '単語登録数の上限に達しました。プランをアップグレードしてください。');
        }

        return $next($request);
    }
}
```

#### ミドルウェア登録

```php
// app/Http/Kernel.php
protected $middlewareAliases = [
    // ...
    'check.word.limit' => \App\Http\Middleware\CheckWordLimit::class,
];
```

#### 適用

```php
Route::post('/words', [MainController::class, 'AddWord'])
    ->middleware(['auth', 'check.word.limit'])
    ->name('words.store');
```

### Userモデルへのリレーション追加（フェーズ2）

```php
// app/Models/User.php
public function subscription()
{
    return $this->hasOne(Subscription::class);
}

public function words()
{
    return $this->hasMany(Word::class);
}

// ヘルパーメソッド
public function canAddWord()
{
    $subscription = $this->subscription;

    if (!$subscription || is_null($subscription->word_limit)) {
        return true; // 無制限
    }

    return $this->words()->count() < $subscription->word_limit;
}

public function remainingWords()
{
    $subscription = $this->subscription;

    if (!$subscription || is_null($subscription->word_limit)) {
        return null; // 無制限
    }

    return max(0, $subscription->word_limit - $this->words()->count());
}
```

### Wordモデルへのuser_id追加（フェーズ2）

#### マイグレーション

```php
Schema::table('words', function (Blueprint $table) {
    $table->foreignId('user_id')->nullable()->after('id')->constrained()->onDelete('cascade');
});
```

#### Word.php

```php
protected $fillable = [
    'user_id',
    'word',
    'part_of_speech',
    'pronunciation_katakana',
    'en_example',
    'jp_example',
];

public function user()
{
    return $this->belongsTo(User::class);
}
```

### プラン情報の表示（全フェーズ共通）

#### 単語一覧ページに表示

- 現在のプラン名
- 登録単語数 / 制限数（例: 15 / 20単語）
- 残り登録可能数
- 「プランをアップグレード」ボタン（フェーズ2以降）

---

## 6. コンポーネント構成

### 新規作成するコントローラー

#### LandingController.php (フェーズ1)

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index()
    {
        // プラン情報を取得（フェーズ2以降はDBから）
        $plans = [
            ['type' => 'free', 'name' => '無料プラン', 'limit' => 20, 'monthly' => 0, 'yearly' => 0],
            ['type' => 'standard', 'name' => 'スタンダード', 'limit' => 50, 'monthly' => 500, 'yearly' => 5000],
            ['type' => 'plus', 'name' => 'プラス', 'limit' => 200, 'monthly' => 1000, 'yearly' => 10000],
            ['type' => 'premium', 'name' => 'プレミアム', 'limit' => null, 'monthly' => 2000, 'yearly' => 20000],
        ];

        return view('landing', compact('plans'));
    }
}
```

#### PricingController.php (フェーズ1)

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PricingController extends Controller
{
    public function index()
    {
        // プラン詳細ページ
        $plans = [
            ['type' => 'free', 'name' => '無料プラン', 'limit' => 20, 'monthly' => 0, 'yearly' => 0],
            ['type' => 'standard', 'name' => 'スタンダード', 'limit' => 50, 'monthly' => 500, 'yearly' => 5000],
            ['type' => 'plus', 'name' => 'プラス', 'limit' => 200, 'monthly' => 1000, 'yearly' => 10000],
            ['type' => 'premium', 'name' => 'プレミアム', 'limit' => null, 'monthly' => 2000, 'yearly' => 20000],
        ];

        return view('pricing', compact('plans'));
    }
}
```

#### SubscriptionController.php (フェーズ2)

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Plan;

class SubscriptionController extends Controller
{
    // 現在のプラン表示
    public function show()
    {
        $subscription = Auth::user()->subscription;
        $wordCount = Auth::user()->words()->count();

        return view('subscription.show', compact('subscription', 'wordCount'));
    }

    // プラン変更ページ
    public function change()
    {
        $plans = Plan::where('is_active', true)->get();
        $currentSubscription = Auth::user()->subscription;

        return view('subscription.change', compact('plans', 'currentSubscription'));
    }

    // フェーズ3で決済処理を追加
    // public function checkout(Request $request) { ... }
    // public function success() { ... }
    // public function cancelCheckout() { ... }
}
```

### 新規作成するビュー

#### landing.blade.php (フェーズ1)

- ヒーローセクション
- 機能紹介セクション
- 無料訴求セクション
- プラン価格表セクション
- 使い方セクション
- フッター

#### pricing.blade.php (フェーズ1)

- 詳細なプラン比較表
- FAQ
- CTA（無料で始める）

#### words/index.blade.php (既存のindex.blade.phpをリネーム・修正)

- プラン情報バー追加（現在のプラン、単語数/制限数）
- 既存の単語一覧機能は維持
- 制限到達時のアラート表示

#### subscription/show.blade.php (フェーズ2)

- 現在のプランの詳細
- 使用状況（単語数）
- プラン変更ボタン
- 決済履歴（フェーズ3）

#### subscription/change.blade.php (フェーズ2)

- プラン選択カード
- 各プランの特徴
- 変更確認ボタン（フェーズ3で決済処理）

### 新規作成するモデル

#### Subscription.php (フェーズ2)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'plan_type', 'word_limit',
        'started_at', 'expires_at', 'status', 'stripe_subscription_id'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_type', 'plan_type');
    }

    public function isUnlimited()
    {
        return is_null($this->word_limit);
    }

    public function isActive()
    {
        return $this->status === 'active';
    }
}
```

#### Plan.php (フェーズ2)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_type', 'name', 'word_limit',
        'monthly_price', 'yearly_price', 'features', 'is_active'
    ];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'plan_type', 'plan_type');
    }
}
```

### 新規作成するミドルウェア

#### CheckWordLimit.php (フェーズ2)

前述の単語登録制限チェックミドルウェア

### 修正が必要な既存ファイル

#### MainController.php

- `ShowIndex` メソッド: ビューパスを `words.index` に変更、user_idでフィルター
- `AddWord` メソッド: user_idを自動追加
- `EditWord` メソッド: ポリシーで権限チェック
- `UpdateWord` メソッド: ポリシーで権限チェック
- `DeleteWord` メソッド: ポリシーで権限チェック

```php
// ShowIndex の修正例
public function ShowIndex(Request $request)
{
    $search = $request->input('search');
    $user = Auth::user();

    if ($search) {
        $words = Word::with('japanese')
            ->where('user_id', $user->id)
            ->where(function($query) use ($search) {
                $query->where('word', 'like', '%' . $search . '%')
                    ->orWhere('en_example', 'like', '%' . $search . '%')
                    ->orWhere('jp_example', 'like', '%' . $search . '%')
                    ->orWhereHas('japanese', function($q) use ($search) {
                        $q->where('japanese', 'like', '%' . $search . '%');
                    });
            })
            ->get();

        $totalCount = Word::where('user_id', $user->id)->count();
    } else {
        $words = Word::with('japanese')->where('user_id', $user->id)->get();
        $totalCount = $words->count();
    }

    // プラン情報を追加（フェーズ2）
    $subscription = $user->subscription;
    $remainingWords = $user->remainingWords();

    return view("words.index", compact('words', 'totalCount', 'subscription', 'remainingWords'));
}

// AddWord の修正例
public function AddWord(Request $request)
{
    $validated = $request->validate([
        'word' => 'required|string|max:255',
        'en_example' => 'nullable|string',
        'jp_example' => 'nullable|string',
        'part_of_speech' => 'nullable|string|max:50',
        'pronunciation_katakana' => 'nullable|string|max:255',
        'meaningArray' => 'required|array|min:1',
        'meaningArray.*' => 'nullable|string',
    ]);

    // user_idを自動追加
    $word = new Word();
    $word->user_id = Auth::id();
    $word->word = $request->word;
    $word->en_example = $request->en_example;
    $word->jp_example = $request->jp_example;
    $word->part_of_speech = $request->part_of_speech;
    $word->pronunciation_katakana = $request->pronunciation_katakana;
    $word->save();

    // Japaneseの保存
    $meanings = $request->input('meaningArray');
    foreach ($meanings as $meaning) {
        if (!empty(trim($meaning))) {
            $japanese = new Japanese();
            $japanese->word_id = $word->id;
            $japanese->japanese = trim($meaning);
            $japanese->save();
        }
    }

    return redirect()->back()->with('success', '単語を登録しました');
}
```

#### routes/web.php

既存ルートを整理・移動（前述のルーティング設計を参照）

#### navigation.blade.php / header.blade.php

ログイン状態に応じたメニュー変更、`/words` へのリンク

---

## 7. エラーハンドリングとバリデーション

### 単語登録制限のユーザー体験

#### 制限到達前の通知

- 残り5単語以下になったら警告バナー表示
- 「あと◯単語で制限に達します。プランのアップグレードをご検討ください。」

#### 制限到達時

- 単語登録フォームを非表示
- 明確なメッセージ: 「単語登録数の上限（20単語）に達しました」
- アップグレードを促すCTA: 「プランをアップグレード」ボタン

#### バリデーションルール（追加）

```php
// MainController@AddWord
$validated = $request->validate([
    'word' => 'required|string|max:255',
    'en_example' => 'nullable|string',
    'jp_example' => 'nullable|string',
    'part_of_speech' => 'nullable|string|max:50',
    'pronunciation_katakana' => 'nullable|string|max:255',
    'meaningArray' => 'required|array|min:1',
    'meaningArray.*' => 'nullable|string',
]);

// ミドルウェアで制限チェック済み
// user_idを自動追加
$validated['user_id'] = Auth::id();
```

### 認証エラー

#### 未ログインで保護されたページにアクセス

- `/words` など → `/login` へリダイレクト
- フラッシュメッセージ: 「ログインが必要です」

#### セッション切れ

- Ajaxリクエスト（AI補完など）で401エラー → ログインページへ誘導

### プラン関連エラー（フェーズ2以降）

#### プランが見つからない

- ユーザーにサブスクリプションがない場合、自動で無料プランを作成

#### 無効なプラン変更リクエスト

- 存在しないプランタイプ → エラーメッセージ表示

### Stripe関連エラー（フェーズ3）

#### 決済失敗

- カード情報エラー → わかりやすいメッセージ
- リトライ可能な処理

#### Webhookエラー

- ログに記録し、管理者に通知
- ユーザーには影響しないよう設計

---

## 8. セキュリティとプライバシー

### 認証とアクセス制御

#### 基本方針

- Laravel Breezeの認証機能を活用
- すべての単語データは user_id で紐付け、他ユーザーからアクセス不可
- ミドルウェアで保護: `/words`, `/subscription` など

#### ポリシー実装（フェーズ2）

```php
<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Word;

class WordPolicy
{
    public function view(User $user, Word $word)
    {
        return $user->id === $word->user_id;
    }

    public function update(User $user, Word $word)
    {
        return $user->id === $word->user_id;
    }

    public function delete(User $user, Word $word)
    {
        return $user->id === $word->user_id;
    }
}
```

#### コントローラーでの適用

```php
// MainController@EditWord
$word = Word::findOrFail($id);
$this->authorize('view', $word);
```

### データプライバシー

#### ユーザーデータの分離

- 各ユーザーは自分の単語のみ閲覧・編集可能
- 単語取得時は必ず `where('user_id', Auth::id())` でフィルター
- 他ユーザーの単語共有機能は今回実装しない（将来検討）

#### センシティブ情報の保護

- パスワードはハッシュ化（Laravel標準）
- APIキー（Gemini）は `.env` で管理、バージョン管理から除外
- Stripe APIキーも `.env` で管理

#### CSRF保護

- すべてのフォームに `@csrf` トークン
- Ajaxリクエストでも `X-CSRF-TOKEN` ヘッダー送信（既存実装あり）

### Stripe決済のセキュリティ（フェーズ3）

#### PCI DSS準拠

- カード情報は直接サーバーに送信せず、Stripeが処理
- Laravel Cashierを使用してベストプラクティスに従う

#### Webhook署名検証

```php
// WebhookController.php
public function handleStripe(Request $request)
{
    $payload = $request->getContent();
    $sigHeader = $request->header('Stripe-Signature');

    try {
        $event = \Stripe\Webhook::constructEvent(
            $payload,
            $sigHeader,
            config('services.stripe.webhook_secret')
        );
    } catch (\Exception $e) {
        Log::error('Webhook signature verification failed: ' . $e->getMessage());
        return response('Webhook signature verification failed', 400);
    }

    // イベント処理
    $this->handleWebhookEvent($event);

    return response('Webhook handled', 200);
}
```

#### サブスクリプション不正防止

- プラン変更はログイン必須
- 決済完了までプランは変更しない
- Webhookで正式な変更を反映

### 環境変数管理

#### 必要な環境変数（フェーズ別）

**フェーズ1:**
```env
GEMINI_API_KEY=your_gemini_api_key
APP_URL=https://yourdomain.com
```

**フェーズ3追加:**
```env
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
```

### ログとモニタリング

#### 重要な操作をログに記録

- ユーザー登録
- プラン変更（フェーズ2以降）
- 決済成功/失敗（フェーズ3）
- Webhookイベント（フェーズ3）

---

## 9. フェーズ別実装詳細

### フェーズ1: ランディングページ + ルーティング変更

#### 実装タスク

1. **ランディングページ作成**
   - `LandingController` 作成
   - `landing.blade.php` 作成（ヒーロー、機能、価格、使い方、フッター）
   - `pricing.blade.php` 作成
   - プラン情報を配列で静的に定義

2. **ルーティング変更**
   - `/` を `LandingController@index` に変更
   - `/words` に既存の単語一覧を移動
   - `/pricing` を追加
   - 認証ミドルウェアを `/words` 以下に適用

3. **既存ファイル修正**
   - `MainController@ShowIndex` のビューパスを `words.index` に変更
   - `index.blade.php` を `resources/views/words/index.blade.php` に移動
   - ナビゲーションメニューの更新（ログイン状態で表示切り替え）
   - リダイレクト設定（ログイン後 → `/words`）

4. **デザイン調整**
   - Tailwind CSSで既存のスタイルを踏襲
   - レスポンシブ対応確認

#### 成果物チェックリスト

- [ ] `/` にアクセスするとランディングページが表示される
- [ ] 未ログインで `/words` にアクセスするとログインページにリダイレクトされる
- [ ] ログイン後に `/words` で単語一覧が表示される
- [ ] ランディングページにプラン価格が表示される
- [ ] モバイルで正しく表示される

---

### フェーズ2: プラン管理機能

#### 実装タスク

1. **データベース準備**
   - `plans` テーブルのマイグレーション作成・実行
   - `subscriptions` テーブルのマイグレーション作成・実行
   - `words` テーブルに `user_id` カラム追加のマイグレーション
   - Seederでプランマスターデータ投入

2. **モデル作成**
   - `Plan` モデル作成
   - `Subscription` モデル作成
   - `User` モデルにリレーション追加
   - `Word` モデルに `user_id` 追加、リレーション追加

3. **プラン管理機能**
   - `SubscriptionController` 作成
   - ユーザー登録時に無料プラン自動付与の処理を追加
   - プラン表示ページ作成
   - プラン変更ページ作成（手動変更、フェーズ3で決済連携）

4. **単語登録制限**
   - `CheckWordLimit` ミドルウェア作成
   - `/words` (POST) にミドルウェア適用
   - 制限到達時のUI表示（警告バナー、登録フォーム非表示）
   - 残り単語数の表示

5. **ポリシー作成**
   - `WordPolicy` 作成
   - コントローラーでポリシー適用

6. **既存データ移行**
   - 既存の単語データに `user_id` を設定（現在のユーザーに紐付け）
   - 現在のユーザーに無料プランを付与

#### 成果物チェックリスト

- [ ] 新規登録時に自動で無料プラン（20単語制限）が付与される
- [ ] 単語一覧ページに現在のプラン情報が表示される
- [ ] 20単語登録後、新規登録ができなくなる
- [ ] 制限到達時にアップグレードを促すメッセージが表示される
- [ ] ユーザーは自分の単語のみ閲覧・編集できる
- [ ] 他ユーザーの単語にアクセスすると403エラーになる

---

### フェーズ3: Stripe連携

#### 実装タスク

1. **Laravel Cashier導入**
   - `composer require laravel/cashier-stripe`
   - マイグレーション実行（Billableトレイト用）
   - `User` モデルに `Billable` トレイト追加
   - Stripe APIキー設定

2. **サブスクリプション購入フロー**
   - Stripe Checkout Sessionの作成
   - 成功/キャンセルページ作成
   - プラン選択からCheckoutへの導線

3. **Webhook処理**
   - Webhookルート作成（CSRF除外）
   - Webhook署名検証
   - イベント処理（subscription.created, subscription.updated, subscription.deleted, invoice.payment_succeeded, invoice.payment_failed）
   - プラン情報の自動更新

4. **サブスクリプション管理**
   - プランのアップグレード/ダウングレード
   - キャンセル機能
   - 再開機能
   - 決済履歴表示
   - 領収書ダウンロード

5. **テスト決済**
   - Stripeテストモードで動作確認
   - Webhookのテスト（Stripe CLI使用）

#### 成果物チェックリスト

- [ ] プラン選択から決済まで完了できる
- [ ] 決済成功後、自動でプランがアップグレードされる
- [ ] Webhookで正しくプラン情報が更新される
- [ ] サブスクリプションをキャンセル/再開できる
- [ ] 決済履歴が正しく表示される
- [ ] テストカードで決済が正常に完了する

---

## 10. テスト戦略とデプロイメント

### テスト方針

#### 手動テスト（各フェーズで実施）

**フェーズ1:**
- ランディングページの表示確認（デスクトップ/モバイル）
- ログイン/ログアウトフロー
- `/` から新規登録 → `/words` への遷移
- 既存の単語機能（登録、編集、削除、テスト、返信アシスタント）が正常動作

**フェーズ2:**
- 新規ユーザー登録時の無料プラン自動付与
- 単語数カウントと制限表示の正確性
- 20単語目の登録成功、21単語目の登録拒否
- プラン情報ページの表示
- 他ユーザーの単語にアクセスできないこと

**フェーズ3:**
- Stripeテストモードでの決済フロー
- サブスクリプション作成後のプラン反映
- Webhookイベントの正常処理
- プランのアップグレード/ダウングレード
- キャンセル処理

### ブラウザ対応

- Chrome（最新版）
- Safari（最新版）
- Firefox（最新版）
- モバイルSafari（iOS）
- Chrome（Android）

### デプロイメント手順（各フェーズ）

#### フェーズ1

```bash
# 1. コードをpush
git add .
git commit -m "feat: Add landing page and update routing"
git push origin main

# 2. 本番環境で
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build
```

#### フェーズ2

```bash
# 1. マイグレーション実行
php artisan migrate

# 2. Seederでプランデータ投入
php artisan db:seed --class=PlanSeeder

# 3. 既存ユーザーに無料プラン付与（必要に応じて）
php artisan db:seed --class=AssignFreePlanSeeder

# 4. キャッシュクリア
php artisan config:clear
php artisan cache:clear
```

#### フェーズ3

```bash
# 1. Cashierマイグレーション
php artisan migrate

# 2. Webhook URLをStripeに登録
# https://yourdomain.com/webhook/stripe

# 3. 環境変数確認
# STRIPE_KEY, STRIPE_SECRET, STRIPE_WEBHOOK_SECRET

# 4. Webhookテスト（ローカル開発時）
stripe listen --forward-to localhost:8000/webhook/stripe
```

### 環境別設定

#### 開発環境

- SQLite使用
- Stripeテストモード
- デバッグモード有効

#### 本番環境

- MySQL/PostgreSQL推奨（スケール対応）
- Stripe本番モード
- デバッグモード無効
- HTTPSが必須（Stripe要件）
- エラーログの監視設定

### バックアップ戦略

#### データベースバックアップ

- 日次自動バックアップ
- バックアップ保持期間: 30日

#### 重要データ

- ユーザー情報
- 単語データ
- サブスクリプション情報
- 決済履歴

### モニタリング

#### 監視項目

- アプリケーションエラー（Laravel Log）
- Stripe Webhookの失敗
- 決済エラー率
- ユーザー登録数
- サブスクリプション状況

#### アラート設定

- Webhook失敗が続いた場合
- 決済エラーが急増した場合
- サーバーエラーが発生した場合

---

## まとめ

この設計書に基づき、3つのフェーズで段階的にマネタイズ機能とランディングページを実装します。

**フェーズ1**: ランディングページで新規ユーザーを獲得
**フェーズ2**: プラン管理で無料/有料の区別を実装
**フェーズ3**: Stripe連携で自動課金を実現

各フェーズで動作確認を行いながら進めることで、安全かつ確実に機能を追加できます。

---

**次のステップ**: この設計書を基に実装計画を作成し、フェーズ1から実装を開始します。
