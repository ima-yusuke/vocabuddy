# 英単語自動補完機能 Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** 英単語入力だけで辞書API + AI整形により意味・品詞・例文・発音を自動生成し、ワンタップで登録できる機能を実装する。

**Architecture:** Free Dictionary APIで基本情報を取得し、Gemini APIで日本人学習者向けに整形。既存のGemini統合を活用し、新規WordAutoCompleteControllerで処理。フロントエンドはBlade + vanilla JavaScriptでプレビュー機能を実装。

**Tech Stack:** Laravel 11, Blade, Tailwind CSS, Free Dictionary API, Gemini API (gemini-3-flash-preview)

---

## Task 1: データベースマイグレーション

**Files:**
- Create: `database/migrations/2026_03_09_add_pronunciation_and_pos_to_words_table.php`
- Modify: `app/Models/Word.php:12-14`

**Step 1: Create migration file**

Run:
```bash
php artisan make:migration add_pronunciation_and_pos_to_words_table
```

Expected: Migration file created in `database/migrations/`

**Step 2: Write migration code**

Edit: `database/migrations/2026_03_09_XXXXXX_add_pronunciation_and_pos_to_words_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('words', function (Blueprint $table) {
            $table->string('part_of_speech')->nullable()->after('word');
            $table->string('pronunciation')->nullable()->after('part_of_speech');
            $table->string('pronunciation_katakana')->nullable()->after('pronunciation');
        });
    }

    public function down(): void
    {
        Schema::table('words', function (Blueprint $table) {
            $table->dropColumn(['part_of_speech', 'pronunciation', 'pronunciation_katakana']);
        });
    }
};
```

**Step 3: Run migration**

Run:
```bash
php artisan migrate
```

Expected: Migration successful, new columns added to words table

**Step 4: Update Word model fillable**

Edit: `app/Models/Word.php:12-14`

```php
protected $fillable = [
    'word',
    'part_of_speech',
    'pronunciation',
    'pronunciation_katakana',
];
```

**Step 5: Verify database changes**

Run:
```bash
php artisan tinker
```

Then:
```php
Schema::hasColumn('words', 'pronunciation');
Schema::hasColumn('words', 'part_of_speech');
Schema::hasColumn('words', 'pronunciation_katakana');
```

Expected: All return `true`

**Step 6: Commit**

Run:
```bash
git add database/migrations/*add_pronunciation_and_pos_to_words_table.php app/Models/Word.php
git commit -m "feat: add pronunciation and part_of_speech columns to words table

- Add part_of_speech (品詞)
- Add pronunciation (発音記号)
- Add pronunciation_katakana (カタカナ読み)
- All columns nullable for existing data compatibility

🤖 Generated with [Claude Code](https://claude.com/claude-code)

Co-Authored-By: Claude <noreply@anthropic.com>"
```

---

## Task 2: WordAutoCompleteController作成

**Files:**
- Create: `app/Http/Controllers/WordAutoCompleteController.php`
- Modify: `routes/web.php:24`

**Step 1: Create controller**

Run:
```bash
php artisan make:controller WordAutoCompleteController
```

Expected: Controller created at `app/Http/Controllers/WordAutoCompleteController.php`

**Step 2: Implement autocomplete method skeleton**

Edit: `app/Http/Controllers/WordAutoCompleteController.php`

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WordAutoCompleteController extends Controller
{
    /**
     * 英単語の自動補完
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function autocomplete(Request $request)
    {
        $validated = $request->validate([
            'word' => 'required|string|max:100',
            'context' => 'nullable|string|max:500',
        ]);

        $word = $validated['word'];
        $context = $validated['context'] ?? null;

        try {
            // Step 1: Free Dictionary APIで基本情報を取得
            $dictionaryData = $this->fetchDictionaryData($word);

            // Step 2: Gemini APIで整形
            $aiData = $this->formatWithAI($word, $context, $dictionaryData);

            return response()->json([
                'success' => true,
                'data' => $aiData
            ]);

        } catch (\Exception $e) {
            Log::error('Autocomplete error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'エラーが発生しました: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Free Dictionary APIからデータ取得
     */
    private function fetchDictionaryData(string $word): ?array
    {
        // 次のステップで実装
        return null;
    }

    /**
     * Gemini APIで整形
     */
    private function formatWithAI(string $word, ?string $context, ?array $dictionaryData): array
    {
        // 次のステップで実装
        return [];
    }
}
```

**Step 3: Add route**

Edit: `routes/web.php:24` (after the existing word routes)

```php
Route::post('/word/autocomplete', [WordAutoCompleteController::class, 'autocomplete'])->name('AutocompleteWord');
```

Also add the use statement at the top:
```php
use App\Http\Controllers\WordAutoCompleteController;
```

**Step 4: Test route exists**

Run:
```bash
php artisan route:list | grep autocomplete
```

Expected: Route `POST /word/autocomplete` is listed

**Step 5: Commit**

Run:
```bash
git add app/Http/Controllers/WordAutoCompleteController.php routes/web.php
git commit -m "feat: add WordAutoCompleteController skeleton

- Create autocomplete endpoint
- Add validation for word and context
- Prepare for Dictionary API and Gemini integration

🤖 Generated with [Claude Code](https://claude.com/claude-code)

Co-Authored-By: Claude <noreply@anthropic.com>"
```

---

## Task 3: Free Dictionary API統合

**Files:**
- Modify: `app/Http/Controllers/WordAutoCompleteController.php:34-65`

**Step 1: Implement fetchDictionaryData method**

Edit: `app/Http/Controllers/WordAutoCompleteController.php:34-65`

Replace the `fetchDictionaryData` method:

```php
/**
 * Free Dictionary APIからデータ取得
 */
private function fetchDictionaryData(string $word): ?array
{
    try {
        $response = Http::timeout(10)->get(
            "https://api.dictionaryapi.dev/api/v2/entries/en/{$word}"
        );

        if ($response->successful()) {
            $data = $response->json();

            if (is_array($data) && count($data) > 0) {
                $entry = $data[0];

                return [
                    'word' => $entry['word'] ?? $word,
                    'phonetics' => $entry['phonetics'] ?? [],
                    'meanings' => $entry['meanings'] ?? [],
                ];
            }
        }

        // 404 or other errors - return null
        Log::info("Dictionary API: Word '{$word}' not found or error occurred");
        return null;

    } catch (\Exception $e) {
        Log::warning("Dictionary API exception: " . $e->getMessage());
        return null;
    }
}
```

**Step 2: Test Dictionary API integration manually**

Run:
```bash
php artisan tinker
```

Then:
```php
$controller = new App\Http\Controllers\WordAutoCompleteController();
$method = new ReflectionMethod($controller, 'fetchDictionaryData');
$method->setAccessible(true);
$result = $method->invoke($controller, 'sick');
print_r($result);
```

Expected: Array with word, phonetics, meanings data

**Step 3: Commit**

Run:
```bash
git add app/Http/Controllers/WordAutoCompleteController.php
git commit -m "feat: integrate Free Dictionary API

- Fetch word data from dictionaryapi.dev
- Handle errors and timeouts gracefully
- Return null on failure for fallback to AI-only mode

🤖 Generated with [Claude Code](https://claude.com/claude-code)

Co-Authored-By: Claude <noreply@anthropic.com>"
```

---

## Task 4: Gemini API統合と整形処理

**Files:**
- Modify: `app/Http/Controllers/WordAutoCompleteController.php:67-150`

**Step 1: Implement formatWithAI method**

Edit: `app/Http/Controllers/WordAutoCompleteController.php:67-150`

Replace the `formatWithAI` method:

```php
/**
 * Gemini APIで整形
 */
private function formatWithAI(string $word, ?string $context, ?array $dictionaryData): array
{
    $apiKey = config('services.gemini.api_key');

    if (!$apiKey) {
        throw new \Exception('Gemini API キーが設定されていません。.envファイルにGEMINI_API_KEYを追加してください。');
    }

    // プロンプト作成
    $prompt = $this->buildPrompt($word, $context, $dictionaryData);

    try {
        $response = Http::timeout(30)->post(
            "https://generativelanguage.googleapis.com/v1beta/models/gemini-3-flash-preview:generateContent?key={$apiKey}",
            [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ]
        );

        if ($response->successful()) {
            $result = $response->json();
            $generatedText = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';

            // JSONを抽出してパース
            return $this->parseAIResponse($generatedText);
        } else {
            throw new \Exception('Gemini API request failed: ' . $response->body());
        }
    } catch (\Exception $e) {
        Log::error('Gemini API error: ' . $e->getMessage());
        throw $e;
    }
}

/**
 * プロンプト作成
 */
private function buildPrompt(string $word, ?string $context, ?array $dictionaryData): string
{
    $prompt = "あなたは日本人英語学習者向けの単語帳アシスタントです。\n\n";
    $prompt .= "英単語: {$word}\n";

    if ($context) {
        $prompt .= "文脈: {$context}\n";
    }

    if ($dictionaryData) {
        $prompt .= "\n辞書データ:\n";
        $prompt .= json_encode($dictionaryData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    $prompt .= "\n\n以下の形式でJSON出力してください：\n";
    $prompt .= "{\n";
    $prompt .= '  "part_of_speech": "日本語の品詞（名詞/動詞/形容詞など）",' . "\n";
    $prompt .= '  "pronunciation_katakana": "カタカナ読み",' . "\n";
    $prompt .= '  "meanings": ["意味1", "意味2"],' . "\n";
    $prompt .= '  "en_example": "短く自然な会話例文",' . "\n";
    $prompt .= '  "jp_example": "自然な日本語訳"';

    if ($context) {
        $prompt .= ',' . "\n";
        $prompt .= '  "context_meaning": "文脈での意味"';
    }

    $prompt .= "\n}\n\n";

    $prompt .= "重要:\n";
    if ($context) {
        $prompt .= "- 文脈がある場合、その文脈での意味を優先し、context_meaningに記載\n";
    }
    $prompt .= "- 意味は日本人学習者向けに自然な日本語で（辞書的すぎない）\n";
    $prompt .= "- 例文は短く会話的に（InstagramやLINEで使えそうな感じ）\n";
    $prompt .= "- 難しい説明は避けて簡潔に\n";
    $prompt .= "- 必ずJSON形式のみで出力（マークダウンコードブロックは不要）\n";

    return $prompt;
}

/**
 * AIレスポンスからJSONをパース
 */
private function parseAIResponse(string $text): array
{
    // JSONブロックを抽出（```jsonで囲まれている場合）
    if (preg_match('/```json\s*(\{.*?\})\s*```/s', $text, $matches)) {
        $jsonText = $matches[1];
    } elseif (preg_match('/(\{.*?\})/s', $text, $matches)) {
        $jsonText = $matches[1];
    } else {
        throw new \Exception('JSON形式のレスポンスが見つかりませんでした');
    }

    $data = json_decode($jsonText, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new \Exception('JSONのパースに失敗しました: ' . json_last_error_msg());
    }

    // 必須フィールドの確認
    if (!isset($data['meanings']) || !is_array($data['meanings'])) {
        throw new \Exception('意味(meanings)が見つかりませんでした');
    }

    return $data;
}
```

**Step 2: Test Gemini integration manually**

Run:
```bash
php artisan tinker
```

Then:
```php
$controller = new App\Http\Controllers\WordAutoCompleteController();
$request = new Illuminate\Http\Request(['word' => 'sick', 'context' => 'That was sick.']);
$response = $controller->autocomplete($request);
echo $response->getContent();
```

Expected: JSON response with success:true and formatted data

**Step 3: Commit**

Run:
```bash
git add app/Http/Controllers/WordAutoCompleteController.php
git commit -m "feat: integrate Gemini API for word formatting

- Build context-aware prompts for Gemini
- Parse JSON responses from AI
- Handle errors and invalid JSON gracefully
- Generate learner-friendly meanings and examples

🤖 Generated with [Claude Code](https://claude.com/claude-code)

Co-Authored-By: Claude <noreply@anthropic.com>"
```

---

## Task 5: フロントエンド - 補完ボタンとプレビューUI

**Files:**
- Modify: `resources/views/index.blade.php:19-66`

**Step 1: Add autocomplete button and context field**

Edit: `resources/views/index.blade.php:19-28`

Replace the word input section with:

```blade
<div>
    <label for="word" class="block text-sm font-semibold text-primary-900 mb-2">
        英単語 <span class="text-red-500">*</span>
    </label>
    <input type="text" id="word" name="word" placeholder="例: serendipity"
        class="w-full border-2 border-primary-200 rounded-xl px-5 py-3 focus:outline-none focus:border-accent-500 focus:ring-4 focus:ring-accent-100 transition-all duration-200 bg-white/50 backdrop-blur-sm text-primary-900 placeholder-primary-400">
</div>

<div>
    <label for="context" class="block text-sm font-semibold text-primary-900 mb-2">
        文脈（オプション）
    </label>
    <input type="text" id="context" placeholder="例: That was sick."
        class="w-full border-2 border-primary-200 rounded-xl px-5 py-3 focus:outline-none focus:border-accent-500 focus:ring-4 focus:ring-accent-100 transition-all duration-200 bg-white/50 backdrop-blur-sm text-primary-900 placeholder-primary-400">
</div>

<div>
    <button type="button" id="autocomplete_btn"
        class="w-full bg-gradient-to-r from-accent-600 to-accent-700 hover:from-accent-700 hover:to-accent-800 text-white px-6 py-4 rounded-xl font-semibold shadow-soft hover:shadow-soft-lg transition-all duration-300 transform hover:-translate-y-0.5">
        💡 AIで補完する
    </button>
</div>

<div id="loading_message" class="hidden text-center text-primary-700 font-semibold">
    <div class="animate-pulse">⏳ <span id="loading_text">辞書データを取得中...</span></div>
</div>

<div id="preview_area" class="hidden space-y-6 border-t-2 border-accent-200 pt-6">
    <h3 class="text-lg font-bold text-primary-900">📝 補完結果（編集可能）</h3>
</div>
```

**Step 2: Add preview fields in preview_area**

Edit: `resources/views/index.blade.php` - Add inside `#preview_area` div:

```blade
<div id="preview_area" class="hidden space-y-6 border-t-2 border-accent-200 pt-6">
    <h3 class="text-lg font-bold text-primary-900">📝 補完結果（編集可能）</h3>

    <div>
        <label for="part_of_speech" class="block text-sm font-semibold text-primary-900 mb-2">
            品詞
        </label>
        <input type="text" id="part_of_speech" name="part_of_speech" placeholder="例: 名詞"
            class="w-full border-2 border-primary-200 rounded-xl px-5 py-3 focus:outline-none focus:border-accent-500 focus:ring-4 focus:ring-accent-100 transition-all duration-200 bg-white/50 backdrop-blur-sm text-primary-900 placeholder-primary-400">
    </div>

    <div>
        <label for="pronunciation" class="block text-sm font-semibold text-primary-900 mb-2">
            発音記号
        </label>
        <input type="text" id="pronunciation" name="pronunciation" placeholder="例: /ˌserənˈdɪpɪti/"
            class="w-full border-2 border-primary-200 rounded-xl px-5 py-3 focus:outline-none focus:border-accent-500 focus:ring-4 focus:ring-accent-100 transition-all duration-200 bg-white/50 backdrop-blur-sm text-primary-900 placeholder-primary-400">
    </div>

    <div>
        <label for="pronunciation_katakana" class="block text-sm font-semibold text-primary-900 mb-2">
            カタカナ読み
        </label>
        <input type="text" id="pronunciation_katakana" name="pronunciation_katakana" placeholder="例: セレンディピティ"
            class="w-full border-2 border-primary-200 rounded-xl px-5 py-3 focus:outline-none focus:border-accent-500 focus:ring-4 focus:ring-accent-100 transition-all duration-200 bg-white/50 backdrop-blur-sm text-primary-900 placeholder-primary-400">
    </div>

    <div id="meanings_container">
        <!-- 意味フィールドが動的に追加される -->
    </div>
</div>
```

**Step 3: Update existing meaning fields to be hidden initially**

Edit: `resources/views/index.blade.php:30-43`

Wrap the existing meaning field in a div and hide it:

```blade
<div id="manual_meanings" class="space-y-6">
    <div>
        <label for="jp_word_1" class="block text-sm font-semibold text-primary-900 mb-2">
            意味 1
        </label>
        <input type="text" id="jp_word_1" name="meaningArray[]" placeholder="例: 偶然の幸運な発見"
            class="w-full border-2 border-primary-200 rounded-xl px-5 py-3 focus:outline-none focus:border-accent-500 focus:ring-4 focus:ring-accent-100 transition-all duration-200 bg-white/50 backdrop-blur-sm text-primary-900 placeholder-primary-400">
    </div>

    <button type="button" id="add_meaning"
        class="inline-flex items-center text-sm text-accent-700 hover:text-accent-800 font-semibold transition-colors">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        意味を追加
    </button>
</div>
```

**Step 4: Commit**

Run:
```bash
git add resources/views/index.blade.php
git commit -m "feat: add autocomplete UI elements

- Add context input field
- Add autocomplete button
- Add loading message
- Add preview area with new fields
- Prepare for JavaScript integration

🤖 Generated with [Claude Code](https://claude.com/claude-code)

Co-Authored-By: Claude <noreply@anthropic.com>"
```

---

## Task 6: JavaScript - 補完機能の実装

**Files:**
- Modify: `resources/views/index.blade.php:183-204`

**Step 1: Add autocomplete JavaScript logic**

Edit: `resources/views/index.blade.php:183` - Replace the existing script section:

```javascript
<script>
    const ADD_MEANING_BTN = document.getElementById('add_meaning');
    const AUTOCOMPLETE_BTN = document.getElementById('autocomplete_btn');
    const LOADING_MESSAGE = document.getElementById('loading_message');
    const LOADING_TEXT = document.getElementById('loading_text');
    const PREVIEW_AREA = document.getElementById('preview_area');
    const MANUAL_MEANINGS = document.getElementById('manual_meanings');
    const form = document.getElementById('add_form');
    let count = 2;

    // 既存の意味追加機能
    ADD_MEANING_BTN.addEventListener('click', () => {
        const div = document.createElement('div');
        div.innerHTML = `
            <label for="jp_word_${count}" class="block text-sm font-semibold text-primary-900 mb-2">
                意味 ${count}
            </label>
            <input type="text" id="jp_word_${count}" name="meaningArray[]" placeholder="意味を入力"
                class="w-full border-2 border-primary-200 rounded-xl px-5 py-3 focus:outline-none focus:border-accent-500 focus:ring-4 focus:ring-accent-100 transition-all duration-200 bg-white/50 backdrop-blur-sm text-primary-900 placeholder-primary-400">
        `;
        form.insertBefore(div, ADD_MEANING_BTN);
        count++;
    });

    // 補完ボタンクリック
    AUTOCOMPLETE_BTN.addEventListener('click', async () => {
        const word = document.getElementById('word').value.trim();
        const context = document.getElementById('context').value.trim();

        if (!word) {
            alert('英単語を入力してください');
            return;
        }

        // ローディング表示
        AUTOCOMPLETE_BTN.disabled = true;
        LOADING_MESSAGE.classList.remove('hidden');
        PREVIEW_AREA.classList.add('hidden');
        MANUAL_MEANINGS.classList.add('hidden');

        try {
            // Step 1: 辞書データ取得中
            LOADING_TEXT.textContent = '辞書データを取得中...';

            const response = await fetch('{{ route("AutocompleteWord") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ word, context })
            });

            // Step 2: AI整形中
            LOADING_TEXT.textContent = '🤖 AIで整形中...';

            const data = await response.json();

            if (data.success) {
                // プレビュー表示
                displayPreview(data.data);
                LOADING_MESSAGE.classList.add('hidden');
                PREVIEW_AREA.classList.remove('hidden');
            } else {
                throw new Error(data.error || '補完に失敗しました');
            }

        } catch (error) {
            console.error('Autocomplete error:', error);
            alert('自動補完に失敗しました: ' + error.message + '\n手動で入力してください。');
            LOADING_MESSAGE.classList.add('hidden');
            MANUAL_MEANINGS.classList.remove('hidden');
        } finally {
            AUTOCOMPLETE_BTN.disabled = false;
        }
    });

    // プレビュー表示
    function displayPreview(data) {
        // 品詞
        document.getElementById('part_of_speech').value = data.part_of_speech || '';

        // 発音記号（辞書データから取得）
        const pronunciation = data.pronunciation ||
            (data.dictionary_data?.phonetics?.[0]?.text) || '';
        document.getElementById('pronunciation').value = pronunciation;

        // カタカナ読み
        document.getElementById('pronunciation_katakana').value = data.pronunciation_katakana || '';

        // 意味を表示
        const meaningsContainer = document.getElementById('meanings_container');
        meaningsContainer.innerHTML = '';

        const meanings = data.meanings || [];
        meanings.forEach((meaning, index) => {
            const div = document.createElement('div');
            div.innerHTML = `
                <label class="block text-sm font-semibold text-primary-900 mb-2">
                    意味 ${index + 1} ${index === 0 ? '<span class="text-red-500">*</span>' : ''}
                </label>
                <div class="flex gap-2">
                    <input type="text" name="meaningArray[]" value="${escapeHtml(meaning)}"
                        class="flex-1 border-2 border-primary-200 rounded-xl px-5 py-3 focus:outline-none focus:border-accent-500 focus:ring-4 focus:ring-accent-100 transition-all duration-200 bg-white/50 backdrop-blur-sm text-primary-900 placeholder-primary-400">
                    ${index > 0 ? `<button type="button" onclick="this.parentElement.parentElement.remove()" class="text-red-500 hover:text-red-700">×</button>` : ''}
                </div>
            `;
            meaningsContainer.appendChild(div);
        });

        // 意味追加ボタン
        const addBtn = document.createElement('button');
        addBtn.type = 'button';
        addBtn.className = 'inline-flex items-center text-sm text-accent-700 hover:text-accent-800 font-semibold transition-colors';
        addBtn.innerHTML = `
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            意味を追加
        `;
        addBtn.addEventListener('click', () => {
            const newDiv = document.createElement('div');
            const nextIndex = meaningsContainer.querySelectorAll('input[name="meaningArray[]"]').length + 1;
            newDiv.innerHTML = `
                <label class="block text-sm font-semibold text-primary-900 mb-2">
                    意味 ${nextIndex}
                </label>
                <div class="flex gap-2">
                    <input type="text" name="meaningArray[]" placeholder="意味を入力"
                        class="flex-1 border-2 border-primary-200 rounded-xl px-5 py-3 focus:outline-none focus:border-accent-500 focus:ring-4 focus:ring-accent-100 transition-all duration-200 bg-white/50 backdrop-blur-sm text-primary-900 placeholder-primary-400">
                    <button type="button" onclick="this.parentElement.parentElement.remove()" class="text-red-500 hover:text-red-700">×</button>
                </div>
            `;
            meaningsContainer.insertBefore(newDiv, addBtn);
        });
        meaningsContainer.appendChild(addBtn);

        // 例文
        document.getElementById('en_example').value = data.en_example || '';
        document.getElementById('jp_example').value = data.jp_example || '';
    }

    // HTMLエスケープ
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function confirmDelete() {
        return confirm('本当にこの単語を削除しますか？');
    }
</script>
```

**Step 2: Test autocomplete functionality**

Run:
```bash
php artisan serve
```

Visit: http://localhost:8000

Test:
1. Enter word "sick"
2. Enter context "That was sick."
3. Click "AIで補完する"
4. Verify loading messages appear
5. Verify preview area shows with formatted data

**Step 3: Commit**

Run:
```bash
git add resources/views/index.blade.php
git commit -m "feat: implement autocomplete JavaScript functionality

- Add autocomplete button click handler
- Show loading states (dictionary → AI)
- Display preview with editable fields
- Support dynamic meaning addition/removal
- Handle errors gracefully with fallback

🤖 Generated with [Claude Code](https://claude.com/claude-code)

Co-Authored-By: Claude <noreply@anthropic.com>"
```

---

## Task 7: MainController - AddWord更新

**Files:**
- Modify: `app/Http/Controllers/MainController.php:37-59`

**Step 1: Update AddWord to handle new fields**

Edit: `app/Http/Controllers/MainController.php:37-59`

Replace the `AddWord` method:

```php
public function AddWord(Request $request){

    // フォームから送信された意味を配列として取得
    $meanings = $request->input('meaningArray');

    // データベースにデータを保存する
    $word = new Word();
    $word->word = $request->word;
    $word->part_of_speech = $request->part_of_speech;
    $word->pronunciation = $request->pronunciation;
    $word->pronunciation_katakana = $request->pronunciation_katakana;
    $word->en_example = $request->en_example;
    $word->jp_example = $request->jp_example;
    $word->save();

    // Japaneseの保存
    if ($meanings && is_array($meanings)) {
        for ($i = 0; $i < count($meanings); $i++) {
            if (!empty($meanings[$i])) {  // 空の意味はスキップ
                $japanese = new Japanese();
                $japanese->word_id = $word->id;
                $japanese->japanese = $meanings[$i];
                $japanese->save();
            }
        }
    }

    return redirect()->back();
}
```

**Step 2: Test word registration manually**

Run:
```bash
php artisan serve
```

Test:
1. Fill out form with autocomplete
2. Edit fields if needed
3. Click "登録する"
4. Verify word is saved with new fields

**Step 3: Commit**

Run:
```bash
git add app/Http/Controllers/MainController.php
git commit -m "feat: update AddWord to save new fields

- Save part_of_speech
- Save pronunciation
- Save pronunciation_katakana
- Skip empty meanings

🤖 Generated with [Claude Code](https://claude.com/claude-code)

Co-Authored-By: Claude <noreply@anthropic.com>"
```

---

## Task 8: 単語一覧での表示改善

**Files:**
- Modify: `resources/views/index.blade.php:128-158`

**Step 1: Update word display to show new fields**

Edit: `resources/views/index.blade.php:130-147`

Replace the word display section:

```blade
<div class="flex items-start justify-between mb-4">
    <div class="flex-1">
        <h2 class="text-2xl font-bold text-primary-900 group-hover:text-accent-700 transition-colors">
            {{$word["word"]}}
            @if($word->part_of_speech)
                <span class="text-sm text-primary-600 font-normal ml-2">
                    ({{$word->part_of_speech}})
                </span>
            @endif
        </h2>

        @if($word->pronunciation || $word->pronunciation_katakana)
            <p class="text-sm text-primary-500 mt-1">
                🔊
                @if($word->pronunciation)
                    <span class="font-mono">{{$word->pronunciation}}</span>
                @endif
                @if($word->pronunciation_katakana)
                    <span class="ml-2">({{$word->pronunciation_katakana}})</span>
                @endif
            </p>
        @endif
    </div>

    @hasanyrole('membership')
        <form method="post" action="{{route('DeleteWord')}}" onsubmit="return confirmDelete()">
            @csrf
            @method('DELETE')
            <input type="hidden" name="id" value="{{$word["id"]}}">
            <button type="submit"
                class="text-primary-300 hover:text-red-600 transition-colors p-2 rounded-lg hover:bg-red-50">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </button>
        </form>
    @endhasanyrole
</div>
```

**Step 2: Test display**

Run:
```bash
php artisan serve
```

Visit: http://localhost:8000

Verify:
- Words with new fields display pronunciation and part of speech
- Words without new fields (existing data) display normally
- No errors for null values

**Step 3: Commit**

Run:
```bash
git add resources/views/index.blade.php
git commit -m "feat: display pronunciation and part of speech in word list

- Show part of speech next to word name
- Display pronunciation symbol and katakana
- Handle null values gracefully
- Maintain existing layout for words without new data

🤖 Generated with [Claude Code](https://claude.com/claude-code)

Co-Authored-By: Claude <noreply@anthropic.com>"
```

---

## Task 9: 単語編集機能 - ルートとコントローラー

**Files:**
- Modify: `app/Http/Controllers/MainController.php:96-130`
- Modify: `routes/web.php:15-16`

**Step 1: Add edit and update methods to MainController**

Edit: `app/Http/Controllers/MainController.php:96`

Add after the `DeleteWord` method:

```php
public function EditWord($id)
{
    $word = Word::with('japanese')->findOrFail($id);
    return view('edit-word', compact('word'));
}

public function UpdateWord(Request $request, $id)
{
    $word = Word::findOrFail($id);

    // 単語本体を更新
    $word->part_of_speech = $request->part_of_speech;
    $word->pronunciation = $request->pronunciation;
    $word->pronunciation_katakana = $request->pronunciation_katakana;
    $word->en_example = $request->en_example;
    $word->jp_example = $request->jp_example;
    $word->save();

    // 既存の意味を削除
    $word->japanese()->delete();

    // 新しい意味を保存
    $meanings = $request->input('meaningArray');
    if ($meanings && is_array($meanings)) {
        foreach ($meanings as $meaning) {
            if (!empty($meaning)) {
                $japanese = new Japanese();
                $japanese->word_id = $word->id;
                $japanese->japanese = $meaning;
                $japanese->save();
            }
        }
    }

    return redirect()->route('ShowIndex')->with('success', '単語を更新しました');
}
```

**Step 2: Add routes**

Edit: `routes/web.php:15-16`

Add after existing word routes:

```php
Route::get('/word/edit/{id}', [MainController::class, 'EditWord'])->name('EditWord');
Route::put('/word/update/{id}', [MainController::class, 'UpdateWord'])->name('UpdateWord');
```

**Step 3: Test routes exist**

Run:
```bash
php artisan route:list | grep -E "EditWord|UpdateWord"
```

Expected: Routes listed

**Step 4: Commit**

Run:
```bash
git add app/Http/Controllers/MainController.php routes/web.php
git commit -m "feat: add edit and update methods for words

- Add EditWord to display edit form
- Add UpdateWord to save changes
- Support updating new fields (pronunciation, part of speech)
- Delete and recreate meanings on update

🤖 Generated with [Claude Code](https://claude.com/claude-code)

Co-Authored-By: Claude <noreply@anthropic.com>"
```

---

## Task 10: 単語編集画面の作成

**Files:**
- Create: `resources/views/edit-word.blade.php`

**Step 1: Create edit view**

Create: `resources/views/edit-word.blade.php`

```blade
<x-template title="単語を編集">
    <div class="min-h-screen bg-gradient-to-br from-primary-50 via-white to-accent-50">
        <section class="py-16 px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto">
                <div class="mb-6">
                    <a href="{{route('ShowIndex')}}" class="text-primary-700 hover:text-primary-900 flex items-center">
                        ← 単語一覧に戻る
                    </a>
                </div>

                <div class="bg-white/80 backdrop-blur-sm border border-primary-100 rounded-2xl p-10 shadow-soft-lg">
                    <div class="flex items-center mb-8">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-accent-400 to-accent-600 flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-primary-900">
                            単語を編集: "{{$word->word}}"
                        </h2>
                    </div>

                    <form method="post" action="{{route('UpdateWord', $word->id)}}" class="space-y-6" id="edit_form">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="block text-sm font-semibold text-primary-900 mb-2">
                                英単語（変更不可）
                            </label>
                            <input type="text" value="{{$word->word}}" readonly
                                class="w-full border-2 border-gray-300 rounded-xl px-5 py-3 bg-gray-100 text-gray-600">
                        </div>

                        <div>
                            <label for="context" class="block text-sm font-semibold text-primary-900 mb-2">
                                文脈（オプション）
                            </label>
                            <input type="text" id="context" placeholder="例: That was sick."
                                class="w-full border-2 border-primary-200 rounded-xl px-5 py-3 focus:outline-none focus:border-accent-500 focus:ring-4 focus:ring-accent-100 transition-all duration-200 bg-white/50 backdrop-blur-sm text-primary-900 placeholder-primary-400">
                        </div>

                        <div>
                            <button type="button" id="autocomplete_btn"
                                class="w-full bg-gradient-to-r from-accent-600 to-accent-700 hover:from-accent-700 hover:to-accent-800 text-white px-6 py-4 rounded-xl font-semibold shadow-soft hover:shadow-soft-lg transition-all duration-300 transform hover:-translate-y-0.5">
                                💡 AIで補完する
                            </button>
                        </div>

                        <div id="loading_message" class="hidden text-center text-primary-700 font-semibold">
                            <div class="animate-pulse">⏳ <span id="loading_text">辞書データを取得中...</span></div>
                        </div>

                        <div class="space-y-6">
                            <div>
                                <label for="part_of_speech" class="block text-sm font-semibold text-primary-900 mb-2">
                                    品詞
                                </label>
                                <input type="text" id="part_of_speech" name="part_of_speech" value="{{$word->part_of_speech}}" placeholder="例: 名詞"
                                    class="w-full border-2 border-primary-200 rounded-xl px-5 py-3 focus:outline-none focus:border-accent-500 focus:ring-4 focus:ring-accent-100 transition-all duration-200 bg-white/50 backdrop-blur-sm text-primary-900 placeholder-primary-400">
                            </div>

                            <div>
                                <label for="pronunciation" class="block text-sm font-semibold text-primary-900 mb-2">
                                    発音記号
                                </label>
                                <input type="text" id="pronunciation" name="pronunciation" value="{{$word->pronunciation}}" placeholder="例: /sɪk/"
                                    class="w-full border-2 border-primary-200 rounded-xl px-5 py-3 focus:outline-none focus:border-accent-500 focus:ring-4 focus:ring-accent-100 transition-all duration-200 bg-white/50 backdrop-blur-sm text-primary-900 placeholder-primary-400">
                            </div>

                            <div>
                                <label for="pronunciation_katakana" class="block text-sm font-semibold text-primary-900 mb-2">
                                    カタカナ読み
                                </label>
                                <input type="text" id="pronunciation_katakana" name="pronunciation_katakana" value="{{$word->pronunciation_katakana}}" placeholder="例: シック"
                                    class="w-full border-2 border-primary-200 rounded-xl px-5 py-3 focus:outline-none focus:border-accent-500 focus:ring-4 focus:ring-accent-100 transition-all duration-200 bg-white/50 backdrop-blur-sm text-primary-900 placeholder-primary-400">
                            </div>

                            <div id="meanings_container">
                                @foreach($word->japanese as $index => $ja)
                                    <div class="mb-4">
                                        <label class="block text-sm font-semibold text-primary-900 mb-2">
                                            意味 {{$index + 1}} @if($index === 0)<span class="text-red-500">*</span>@endif
                                        </label>
                                        <div class="flex gap-2">
                                            <input type="text" name="meaningArray[]" value="{{$ja->japanese}}"
                                                class="flex-1 border-2 border-primary-200 rounded-xl px-5 py-3 focus:outline-none focus:border-accent-500 focus:ring-4 focus:ring-accent-100 transition-all duration-200 bg-white/50 backdrop-blur-sm text-primary-900 placeholder-primary-400">
                                            @if($index > 0)
                                                <button type="button" onclick="this.parentElement.parentElement.remove()" class="text-red-500 hover:text-red-700 text-2xl px-3">×</button>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <button type="button" id="add_meaning"
                                class="inline-flex items-center text-sm text-accent-700 hover:text-accent-800 font-semibold transition-colors">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                意味を追加
                            </button>

                            <div>
                                <label for="en_example" class="block text-sm font-semibold text-primary-900 mb-2">
                                    例文（英語）
                                </label>
                                <textarea id="en_example" name="en_example" placeholder="例: That game was sick!"
                                    class="w-full border-2 border-primary-200 rounded-xl px-5 py-3 focus:outline-none focus:border-accent-500 focus:ring-4 focus:ring-accent-100 resize-none transition-all duration-200 bg-white/50 backdrop-blur-sm text-primary-900 placeholder-primary-400" rows="3">{{$word->en_example}}</textarea>
                            </div>

                            <div>
                                <label for="jp_example" class="block text-sm font-semibold text-primary-900 mb-2">
                                    例文（日本語）
                                </label>
                                <textarea id="jp_example" name="jp_example" placeholder="例: あのゲーム最高だったね！"
                                    class="w-full border-2 border-primary-200 rounded-xl px-5 py-3 focus:outline-none focus:border-accent-500 focus:ring-4 focus:ring-accent-100 resize-none transition-all duration-200 bg-white/50 backdrop-blur-sm text-primary-900 placeholder-primary-400" rows="3">{{$word->jp_example}}</textarea>
                            </div>

                            <div class="flex gap-4">
                                <button type="submit"
                                    class="flex-1 bg-gradient-to-r from-primary-800 to-primary-900 hover:from-primary-900 hover:to-primary-800 text-white px-6 py-4 rounded-xl font-semibold shadow-soft hover:shadow-soft-lg transition-all duration-300 transform hover:-translate-y-0.5">
                                    ✓ 更新する
                                </button>
                                <a href="{{route('ShowIndex')}}"
                                    class="flex-1 text-center bg-white hover:bg-gray-50 border-2 border-gray-300 text-gray-700 px-6 py-4 rounded-xl font-semibold shadow-soft hover:shadow-soft-lg transition-all duration-300">
                                    キャンセル
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>

<script>
    const ADD_MEANING_BTN = document.getElementById('add_meaning');
    const AUTOCOMPLETE_BTN = document.getElementById('autocomplete_btn');
    const LOADING_MESSAGE = document.getElementById('loading_message');
    const LOADING_TEXT = document.getElementById('loading_text');
    const MEANINGS_CONTAINER = document.getElementById('meanings_container');
    let meaningCount = {{count($word->japanese)}};

    // 意味追加
    ADD_MEANING_BTN.addEventListener('click', () => {
        meaningCount++;
        const div = document.createElement('div');
        div.className = 'mb-4';
        div.innerHTML = `
            <label class="block text-sm font-semibold text-primary-900 mb-2">
                意味 ${meaningCount}
            </label>
            <div class="flex gap-2">
                <input type="text" name="meaningArray[]" placeholder="意味を入力"
                    class="flex-1 border-2 border-primary-200 rounded-xl px-5 py-3 focus:outline-none focus:border-accent-500 focus:ring-4 focus:ring-accent-100 transition-all duration-200 bg-white/50 backdrop-blur-sm text-primary-900 placeholder-primary-400">
                <button type="button" onclick="this.parentElement.parentElement.remove()" class="text-red-500 hover:text-red-700 text-2xl px-3">×</button>
            </div>
        `;
        MEANINGS_CONTAINER.appendChild(div);
    });

    // 補完ボタン
    AUTOCOMPLETE_BTN.addEventListener('click', async () => {
        const word = '{{$word->word}}';
        const context = document.getElementById('context').value.trim();

        AUTOCOMPLETE_BTN.disabled = true;
        LOADING_MESSAGE.classList.remove('hidden');

        try {
            LOADING_TEXT.textContent = '辞書データを取得中...';

            const response = await fetch('{{ route("AutocompleteWord") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ word, context })
            });

            LOADING_TEXT.textContent = '🤖 AIで整形中...';

            const data = await response.json();

            if (data.success) {
                fillFields(data.data);
                LOADING_MESSAGE.classList.add('hidden');
                alert('補完完了！内容を確認して更新してください。');
            } else {
                throw new Error(data.error || '補完に失敗しました');
            }

        } catch (error) {
            console.error('Autocomplete error:', error);
            alert('自動補完に失敗しました: ' + error.message);
            LOADING_MESSAGE.classList.add('hidden');
        } finally {
            AUTOCOMPLETE_BTN.disabled = false;
        }
    });

    function fillFields(data) {
        if (data.part_of_speech) {
            document.getElementById('part_of_speech').value = data.part_of_speech;
        }
        if (data.pronunciation) {
            document.getElementById('pronunciation').value = data.pronunciation;
        }
        if (data.pronunciation_katakana) {
            document.getElementById('pronunciation_katakana').value = data.pronunciation_katakana;
        }
        if (data.en_example) {
            document.getElementById('en_example').value = data.en_example;
        }
        if (data.jp_example) {
            document.getElementById('jp_example').value = data.jp_example;
        }

        // 意味は上書きせず、空の場合のみ埋める
        if (data.meanings && data.meanings.length > 0) {
            const currentMeanings = MEANINGS_CONTAINER.querySelectorAll('input[name="meaningArray[]"]');
            const emptyCount = Array.from(currentMeanings).filter(input => !input.value.trim()).length;

            if (emptyCount > 0 || currentMeanings.length === 0) {
                // 空の意味フィールドがある場合のみ提案
                if (confirm('AIが生成した意味で置き換えますか？（既存の意味は削除されます）')) {
                    MEANINGS_CONTAINER.innerHTML = '';
                    data.meanings.forEach((meaning, index) => {
                        const div = document.createElement('div');
                        div.className = 'mb-4';
                        div.innerHTML = `
                            <label class="block text-sm font-semibold text-primary-900 mb-2">
                                意味 ${index + 1} ${index === 0 ? '<span class="text-red-500">*</span>' : ''}
                            </label>
                            <div class="flex gap-2">
                                <input type="text" name="meaningArray[]" value="${escapeHtml(meaning)}"
                                    class="flex-1 border-2 border-primary-200 rounded-xl px-5 py-3 focus:outline-none focus:border-accent-500 focus:ring-4 focus:ring-accent-100 transition-all duration-200 bg-white/50 backdrop-blur-sm text-primary-900 placeholder-primary-400">
                                ${index > 0 ? '<button type="button" onclick="this.parentElement.parentElement.remove()" class="text-red-500 hover:text-red-700 text-2xl px-3">×</button>' : ''}
                            </div>
                        `;
                        MEANINGS_CONTAINER.appendChild(div);
                    });
                    meaningCount = data.meanings.length;
                }
            }
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>
</x-template>
```

**Step 2: Test edit page**

Run:
```bash
php artisan serve
```

Visit a word edit page (e.g., http://localhost:8000/word/edit/1)

Verify:
- Existing data is pre-filled
- Autocomplete button works
- Update saves correctly

**Step 3: Commit**

Run:
```bash
git add resources/views/edit-word.blade.php
git commit -m "feat: create word edit page

- Pre-fill existing word data
- Support autocomplete for missing fields
- Allow manual editing of all fields
- Confirm before replacing meanings with AI suggestions

🤖 Generated with [Claude Code](https://claude.com/claude-code)

Co-Authored-By: Claude <noreply@anthropic.com>"
```

---

## Task 11: 単語一覧に編集ボタン追加

**Files:**
- Modify: `resources/views/index.blade.php:130-147`

**Step 1: Add edit button to word cards**

Edit: `resources/views/index.blade.php:142-147`

Replace the delete button section with both edit and delete buttons:

```blade
@hasanyrole('membership')
    <div class="flex gap-2">
        <a href="{{route('EditWord', $word['id'])}}"
            class="text-primary-400 hover:text-accent-600 transition-colors p-2 rounded-lg hover:bg-accent-50">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
        </a>
        <form method="post" action="{{route('DeleteWord')}}" onsubmit="return confirmDelete()">
            @csrf
            @method('DELETE')
            <input type="hidden" name="id" value="{{$word["id"]}}">
            <button type="submit"
                class="text-primary-300 hover:text-red-600 transition-colors p-2 rounded-lg hover:bg-red-50">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </button>
        </form>
    </div>
@endhasanyrole
```

**Step 2: Test edit buttons**

Run:
```bash
php artisan serve
```

Visit: http://localhost:8000

Verify:
- Edit buttons appear next to delete buttons
- Clicking edit navigates to edit page
- Both buttons have proper hover effects

**Step 3: Commit**

Run:
```bash
git add resources/views/index.blade.php
git commit -m "feat: add edit button to word cards

- Display edit icon next to delete button
- Link to edit page for each word
- Maintain existing delete functionality

🤖 Generated with [Claude Code](https://claude.com/claude-code)

Co-Authored-By: Claude <noreply@anthropic.com>"
```

---

## Task 12: エラーハンドリング改善

**Files:**
- Modify: `app/Http/Controllers/WordAutoCompleteController.php:19-33`

**Step 1: Improve error handling with specific messages**

Edit: `app/Http/Controllers/WordAutoCompleteController.php:19-33`

Replace the try-catch in the `autocomplete` method:

```php
try {
    // Step 1: Free Dictionary APIで基本情報を取得
    $dictionaryData = $this->fetchDictionaryData($word);

    if (!$dictionaryData) {
        Log::info("Word '{$word}' not found in dictionary, using AI only");
    }

    // Step 2: Gemini APIで整形
    $aiData = $this->formatWithAI($word, $context, $dictionaryData);

    // 発音記号を辞書データから取得（優先）
    if ($dictionaryData && isset($dictionaryData['phonetics'][0]['text'])) {
        $aiData['pronunciation'] = $dictionaryData['phonetics'][0]['text'];
    }

    return response()->json([
        'success' => true,
        'data' => $aiData,
        'dictionary_data' => $dictionaryData  // フロントエンドで使用可能に
    ]);

} catch (\Exception $e) {
    Log::error('Autocomplete error: ' . $e->getMessage(), [
        'word' => $word,
        'context' => $context,
        'trace' => $e->getTraceAsString()
    ]);

    $errorMessage = 'エラーが発生しました';

    // エラーメッセージを分類
    if (str_contains($e->getMessage(), 'API キーが設定されていません')) {
        $errorMessage = 'Gemini APIキーが設定されていません';
    } elseif (str_contains($e->getMessage(), 'JSON')) {
        $errorMessage = 'AI応答の解析に失敗しました';
    } elseif (str_contains($e->getMessage(), 'timeout')) {
        $errorMessage = 'タイムアウトしました。もう一度試してください';
    }

    return response()->json([
        'success' => false,
        'error' => $errorMessage
    ], 500);
}
```

**Step 2: Test error scenarios**

Test various error cases:
1. Invalid API key
2. Network timeout (simulate by setting very short timeout)
3. Invalid word that causes parsing issues

**Step 3: Commit**

Run:
```bash
git add app/Http/Controllers/WordAutoCompleteController.php
git commit -m "feat: improve error handling and logging

- Add specific error messages for different failure types
- Pass dictionary data to frontend
- Prioritize dictionary pronunciation over AI
- Log errors with context for debugging

🤖 Generated with [Claude Code](https://claude.com/claude-code)

Co-Authored-By: Claude <noreply@anthropic.com>"
```

---

## Task 13: 最終テストとドキュメント更新

**Files:**
- Create: `docs/features/word-autocomplete.md`
- Modify: `README.md`

**Step 1: Comprehensive manual testing**

Test all scenarios:

1. **新規単語登録 - 一般的な単語:**
   - Word: "happy"
   - Verify: pronunciation, meanings, examples populated

2. **新規単語登録 - スラング with context:**
   - Word: "sick"
   - Context: "That was sick."
   - Verify: context-appropriate meaning prioritized

3. **新規単語登録 - 辞書にない単語:**
   - Word: "yolo"
   - Verify: AI-only generation works

4. **既存単語編集:**
   - Edit old word without new fields
   - Use autocomplete
   - Verify: new fields populated, existing meanings preserved

5. **手動入力フォールバック:**
   - Disconnect network
   - Verify: error message, manual input still works

**Step 2: Create feature documentation**

Create: `docs/features/word-autocomplete.md`

```markdown
# 英単語自動補完機能

## 概要

英単語を入力するだけで、辞書APIとAIを使って意味・品詞・例文・発音記号を自動生成する機能。

## 使い方

### 新規単語登録

1. 英単語を入力
2. （オプション）文脈を入力（例: "That was sick."）
3. 「AIで補完する」ボタンをクリック
4. 2-3秒待つ
5. 補完結果を確認・編集
6. 「登録する」ボタンで保存

### 既存単語の編集

1. 単語一覧で編集ボタン（鉛筆アイコン）をクリック
2. 編集画面で「AIで補完する」ボタンを使用
3. 不足している情報（発音、品詞など）が自動補完される
4. 「更新する」ボタンで保存

## 技術仕様

### API統合

1. **Free Dictionary API**
   - 発音記号、品詞、基本的な意味を取得
   - 無料、認証不要
   - URL: `https://api.dictionaryapi.dev/api/v2/entries/en/{word}`

2. **Gemini API**
   - 日本人学習者向けに内容を整形
   - 自然な日本語の意味
   - 会話的な例文を生成
   - 文脈を考慮した意味の優先順位付け

### データフロー

```
英単語 + 文脈（オプション）
  ↓
Free Dictionary API（1-2秒）
  ↓
Gemini API で整形（1-2秒）
  ↓
プレビュー表示（ユーザーが編集可能）
  ↓
DB保存
```

### エラーハンドリング

- 辞書APIエラー → AIのみで生成
- AIエラー → 手動入力にフォールバック
- タイムアウト → エラーメッセージ + 再試行を促す

## データベーススキーマ

### 新規カラム（wordsテーブル）

- `part_of_speech` (string, nullable) - 品詞
- `pronunciation` (string, nullable) - 発音記号
- `pronunciation_katakana` (string, nullable) - カタカナ読み

すべてnullable = 既存データとの互換性を保つ

## 制限事項

- Free Dictionary APIはレート制限あり（450リクエスト/5分）
- 辞書にない造語や最新スラングは辞書APIで取得不可（AIのみで生成）
- Gemini APIの応答時間により2-3秒かかる

## 将来の改善案

- 一括補完機能（既存単語全てを補完）
- 音声再生機能
- お気に入りの意味をハイライト
- オフライン対応（キャッシュ）
```

**Step 3: Update README if needed**

Edit: `README.md`

Add a section about the autocomplete feature or update the features list.

**Step 4: Final commit**

Run:
```bash
git add docs/features/word-autocomplete.md README.md
git commit -m "docs: add word autocomplete feature documentation

- Document usage for new and existing words
- Describe technical architecture
- List API integrations and error handling
- Note limitations and future improvements

🤖 Generated with [Claude Code](https://claude.com/claude-code)

Co-Authored-By: Claude <noreply@anthropic.com>"
```

---

## 完了チェックリスト

実装完了後、以下を確認：

- [ ] データベースマイグレーション実行済み
- [ ] 新規単語登録で補完機能が動作
- [ ] 文脈を考慮した意味が優先表示される
- [ ] 既存単語の編集で補完が使える
- [ ] 単語一覧で発音・品詞が表示される
- [ ] エラー時に手動入力にフォールバック可能
- [ ] すべてのコミットメッセージが適切
- [ ] ドキュメントが更新されている

## 成功基準

1. ✅ 英単語入力 → 補完ボタン → 2-3秒以内にプレビュー表示
2. ✅ 文脈を考慮した意味が優先表示される
3. ✅ 既存の手動入力フローを破壊しない
4. ✅ 既存単語も編集で補完可能
5. ✅ nullカラムを許容し、既存データとの互換性を保つ
6. ✅ エラー時も手動入力でリカバリー可能
