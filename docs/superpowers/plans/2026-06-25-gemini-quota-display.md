# Gemini無料枠 残量表示 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** 単語調べ（gemini-2.5-flash）の無料枠について、毎分・1日あたりの残り回数と回復時間を画面に表示する。

**Architecture:** 既存の `ai_usage_logs` テーブルを読むだけの集計サービス `GeminiQuotaService` を作り、`GET /word/ai-quota` がJSONを返し、単語帳画面のJSが描画する。AI呼び出し本体（autocomplete等）には一切手を入れない読み取り専用機能。

**Tech Stack:** Laravel 11 / PHP / Eloquent / Carbon / PHPUnit / Blade + vanilla JS

---

## File Structure

- **Create:** `app/Services/GeminiQuotaService.php` — 残量・回復時刻の集計ロジック（純粋・テスト可能）
- **Modify:** `config/services.php` — gemini に flash_rpm / flash_rpd 設定を追加
- **Modify:** `app/Http/Controllers/WordAutoCompleteController.php` — `quota()` メソッド追加
- **Modify:** `routes/web.php` — `GET /word/ai-quota` ルート追加
- **Modify:** `resources/views/words/index.blade.php` — 残量表示UI + 取得JS
- **Test:** `tests/Unit/GeminiQuotaServiceTest.php` — 集計ロジックのテスト

---

## Task 1: クォータ設定の追加

**Files:**
- Modify: `config/services.php:38-40`

- [ ] **Step 1: gemini設定にRPM/RPDを追加**

`config/services.php` の `'gemini' => [...]` ブロックを以下に置き換える:

```php
    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        // gemini-2.5-flash 無料枠のレート上限（単語調べ用）
        // flash_rpd は本番429ログで20と確定。flash_rpm はGoogle非公開のため安全側の初期値。
        'flash_rpm' => (int) env('GEMINI_FLASH_RPM', 10),
        'flash_rpd' => (int) env('GEMINI_FLASH_RPD', 20),
    ],
```

- [ ] **Step 2: 設定が読めることを確認**

Run: `php artisan tinker --execute="echo config('services.gemini.flash_rpd');"`
Expected: `20`

- [ ] **Step 3: Commit**

```bash
git add config/services.php
git commit -m "feat: Gemini無料枠のRPM/RPD上限を設定に追加"
```

---

## Task 2: GeminiQuotaService（集計ロジック）

`gemini-2.5-flash` / `type=autocomplete` のログを集計し残量・回復情報を返す。
全ユーザー横断で集計（user_id絞りなし）。

**Files:**
- Create: `app/Services/GeminiQuotaService.php`
- Test: `tests/Unit/GeminiQuotaServiceTest.php`

- [ ] **Step 1: 失敗するテストを書く**

`tests/Unit/GeminiQuotaServiceTest.php` を作成:

```php
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
        $this->assertSame(3, $q['day']['remaining']);     // 20 - 3（全部今日）
    }

    public function test_other_models_are_ignored(): void
    {
        AiUsageLog::create([
            'user_id' => $this->user->id,
            'type' => 'reply',
            'tokens_used' => null,
            'model_used' => 'gemini-3-flash-preview',
            'created_at' => '2026-06-25 12:00:00',
        ]);

        $q = (new GeminiQuotaService())->status();

        $this->assertSame(10, $q['minute']['remaining']); // flash以外は無視
        $this->assertSame(20, $q['day']['remaining']);
    }

    public function test_minute_recovery_seconds_when_exhausted(): void
    {
        // 直近1分に10件（=上限）。最古は12:00:00の40秒前 = 11:59:20
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
}
```

- [ ] **Step 2: テストを実行して失敗を確認**

> **テストDB前提:** `phpunit.xml` は `DB_DATABASE=testing`（MySQL）を使う。`RefreshDatabase` が動くよう、事前にMySQLに空の `testing` データベースが存在する必要がある。無ければ作成: `mysql -u root -e "CREATE DATABASE IF NOT EXISTS testing;"`（接続情報は `.env` に合わせる）。

Run: `php artisan test --filter=GeminiQuotaServiceTest`
Expected: FAIL — `Class "App\Services\GeminiQuotaService" not found`

- [ ] **Step 3: GeminiQuotaService を実装**

`app/Services/GeminiQuotaService.php` を作成:

```php
<?php

namespace App\Services;

use App\Models\AiUsageLog;
use Illuminate\Support\Carbon;

/**
 * Gemini無料枠（gemini-2.5-flash / autocomplete）の残量を ai_usage_logs から集計する。
 * クォータはAPIキー単位で全ユーザー共有のため user_id では絞らない。
 */
class GeminiQuotaService
{
    private const MODEL = 'gemini-2.5-flash';
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
            ->whereDate('created_at', $now->toDateString())
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
        return AiUsageLog::where('type', self::TYPE)
            ->where('model_used', self::MODEL);
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
        return max(0, $now->diffInSeconds($recoversAt, false));
    }
}
```

- [ ] **Step 4: テストを実行して成功を確認**

Run: `php artisan test --filter=GeminiQuotaServiceTest`
Expected: PASS（5テスト）

- [ ] **Step 5: Commit**

```bash
git add app/Services/GeminiQuotaService.php tests/Unit/GeminiQuotaServiceTest.php
git commit -m "feat: Gemini無料枠の残量集計サービスを追加"
```

---

## Task 3: 残量取得エンドポイント

**Files:**
- Modify: `app/Http/Controllers/WordAutoCompleteController.php:8`（use追加）と末尾（メソッド追加）
- Modify: `routes/web.php:49-50` 付近

- [ ] **Step 1: コントローラに use を追加**

`app/Http/Controllers/WordAutoCompleteController.php` の use 群（8行目 `use App\Models\AiUsageLog;` の下）に追加:

```php
use App\Services\GeminiQuotaService;
```

- [ ] **Step 2: quota メソッドを追加**

同ファイルの `autocomplete()` メソッドの直後（クラス内）に追加:

```php
    /**
     * Gemini無料枠（単語調べ）の残量を返す
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function quota(GeminiQuotaService $quota)
    {
        return response()->json([
            'success' => true,
            'data' => $quota->status(),
        ]);
    }
```

- [ ] **Step 3: ルートを追加**

`routes/web.php` の autocomplete ルート（`Route::post('/word/autocomplete'...`）の直後に追加:

```php
    Route::get('/word/ai-quota', [WordAutoCompleteController::class, 'quota'])
        ->name('word.ai-quota');
```

- [ ] **Step 4: エンドポイントが動くことを確認**

Run: `php artisan route:list --path=word/ai-quota`
Expected: `GET|HEAD  word/ai-quota ... word.ai-quota`

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/WordAutoCompleteController.php routes/web.php
git commit -m "feat: 残量取得エンドポイント GET /word/ai-quota を追加"
```

---

## Task 4: 残量表示UI

**Files:**
- Modify: `resources/views/words/index.blade.php`（ボタン下にHTML追加 + JS追加）

- [ ] **Step 1: 残量表示エリアのHTMLを追加**

`resources/views/words/index.blade.php` の autocomplete ボタン（58-61行目）の閉じ `</button>` の直後、`<!-- loading_message -->` の前に挿入:

```blade
                            <div id="ai_quota" class="text-xs text-black/60 mt-2 text-center" style="display:none;">
                                <span id="ai_quota_text"></span>
                            </div>
```

- [ ] **Step 2: 残量取得・描画JSを追加**

同ファイルの `<script>` 内、`const AUTOCOMPLETE_BTN = document.getElementById('autocomplete_btn');`（276行目付近）の直後に追加:

```javascript
    const AI_QUOTA = document.getElementById('ai_quota');
    const AI_QUOTA_TEXT = document.getElementById('ai_quota_text');

    async function refreshAiQuota() {
        try {
            const res = await fetch('/word/ai-quota', {
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            });
            if (!res.ok) return;
            const json = await res.json();
            const q = json.data;

            let text = `今日: 残り ${q.day.remaining}/${q.day.limit} 回 ・ 直近1分: 残り ${q.minute.remaining}/${q.minute.limit} 回`;
            if (q.day.remaining === 0) {
                text = '本日の上限に達しました（翌日0時に回復）';
            } else if (q.minute.remaining === 0) {
                text = `混み合っています。あと ${q.minute.recovers_in_seconds} 秒で回復します`;
            }
            AI_QUOTA_TEXT.textContent = text;
            AI_QUOTA.style.display = 'block';
        } catch (e) {
            // 残量表示は補助情報なので失敗しても無視
        }
    }

    refreshAiQuota();
```

- [ ] **Step 3: autocomplete実行後に残量を更新**

同ファイルの autocomplete の `finally` ブロック（`AUTOCOMPLETE_BTN.disabled = false;` がある箇所、407行目付近）の `AUTOCOMPLETE_BTN.disabled = false;` の直後に追加:

```javascript
            refreshAiQuota();
```

- [ ] **Step 4: ブラウザで目視確認**

Run: `php artisan serve`（別ターミナル）
ログインして単語帳画面（`/`）を開き、AI補完ボタン下に「今日: 残り 20/20 回 ・ 直近1分: 残り 10/10 回」が表示されること、単語をAI補完すると数字が減ることを確認。

- [ ] **Step 5: Commit**

```bash
git add resources/views/words/index.blade.php
git commit -m "feat: 単語調べに残量表示UIを追加"
```

---

## Self-Review メモ

- **Spec coverage:** 毎分残量(Task2,4) / 1日残量(Task2,4) / 回復秒数(Task2,4) / モデル別=flashのみ(Task2 baseQuery) / 設定値で上限保持(Task1) — 全てカバー。
- **Type consistency:** `status()` の返す配列キー（minute.remaining/limit/recovers_in_seconds, day.remaining/limit/recovers_at）はテスト・コントローラ・JSで一致。
- **No placeholders:** 全ステップに実コードあり。
- **注意（実装者向け）:** APP_TIMEZONE は UTC。`whereDate` と `today` はUTC基準で集計される。Geminiの1日リセットが太平洋時間だと数時間ズレる可能性があるが、Specの「既知の限界」通り、まずこの実装で出し、ズレたら補正する。
