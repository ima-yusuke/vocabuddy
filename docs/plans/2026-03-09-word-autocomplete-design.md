# 英単語自動補完機能 設計書

## 概要

英単語を入力するだけで、辞書APIとAIを使って意味・品詞・例文・発音記号などを自動生成し、ワンタップで登録できる機能。

### 目的

- 映画で出会った単語を素早く登録できるようにする
- Googleで調べる手間を削減
- 文脈を考慮した適切な意味を提示

### ユーザーフロー

```
英単語入力（必須） + 文脈入力（オプション）
  ↓
「補完」ボタン押下
  ↓
辞書API → Gemini APIで整形（2-3秒）
  ↓
プレビュー表示（編集可能）
  ↓
「登録」ボタンでDB保存
```

## 採用アプローチ

### アプローチ1: シーケンシャル処理（辞書→AI整形）

**選定理由:**
- コストが安い（Gemini APIは整形のみ）
- 辞書APIの確実な情報（発音記号、品詞）を基盤にできる
- プロンプトがシンプルで安定

**フロー:**
1. Free Dictionary APIで基本情報を取得（発音記号、品詞、意味、例文）
2. 取得したデータをGemini APIに渡して日本人学習者向けに整形
3. フロントエンドで編集可能なプレビューを表示
4. ユーザー確認後にDB保存

## アーキテクチャ

### システム構成

```
┌─────────────────────────────────────────────────────┐
│ フロントエンド (index.blade.php)                      │
│ - 英単語入力フィールド                                 │
│ - 文脈入力フィールド（オプション）                      │
│ - 補完ボタン                                          │
│ - プレビューエリア（編集可能）                         │
│ - 登録ボタン                                          │
└─────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│ バックエンド (WordAutoCompleteController)            │
│                                                     │
│ POST /word/autocomplete                            │
│   1. Free Dictionary API呼び出し                    │
│   2. Gemini APIで整形                               │
│   3. JSON返却                                       │
│                                                     │
│ POST /word/store (既存AddWordを改修)                │
│   - プレビュー内容をDB保存                           │
│                                                     │
│ GET /word/edit/{id}                                │
│ PUT /word/update/{id}                              │
│   - 既存単語の編集機能                               │
└─────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│ 外部API                                              │
│ - Free Dictionary API (無料、認証不要)               │
│ - Gemini API (既存のAPIキー使用)                     │
└─────────────────────────────────────────────────────┘
```

### エンドポイント設計

**1. POST /word/autocomplete**
- リクエスト: `{word: string, context?: string}`
- レスポンス:
```json
{
  "success": true,
  "data": {
    "word": "sick",
    "part_of_speech": "形容詞",
    "pronunciation": "/sɪk/",
    "pronunciation_katakana": "シック",
    "meanings": ["病気の", "最高の（スラング）"],
    "en_example": "That game was sick!",
    "jp_example": "あのゲーム最高だったね！",
    "context_meaning": "最高の"  // 文脈ありの場合
  }
}
```

**2. POST /word/store** (既存AddWordを改修)
- 新規カラムを含むリクエストを処理
- 意味は配列で受け取り（meaningArray[]）

**3. GET /word/edit/{id}**
- 既存単語の編集画面を表示

**4. PUT /word/update/{id}**
- 既存単語を更新（nullを許容）

## データベーススキーマ

### Wordsテーブルの拡張

**新規カラム追加:**
- `part_of_speech` (string, nullable) - 品詞（例: 名詞, 動詞, 形容詞）
- `pronunciation` (string, nullable) - 発音記号（例: /ˈmætʃ/）
- `pronunciation_katakana` (string, nullable) - カタカナ読み（例: マッチ）

**既存カラム:**
- `id` (primary key)
- `word` (string) - 英単語
- `en_example` (string, nullable) - 英語例文
- `jp_example` (string, nullable) - 日本語例文
- `timestamps`

**Japaneseテーブル（変更なし）:**
- `id`, `japanese`, `word_id`, `timestamps`

**マイグレーションファイル:**
`database/migrations/2026_03_09_add_pronunciation_and_pos_to_words_table.php`

**NULL許容の理由:**
- 既存レコードとの互換性
- 辞書APIで情報が取得できない場合がある
- ユーザーが手動入力をスキップする可能性

## API統合

### Free Dictionary API

**エンドポイント:**
```
https://api.dictionaryapi.dev/api/v2/entries/en/{word}
```

**取得データ:**
- 発音記号: `phonetics[].text`
- 品詞: `meanings[].partOfSpeech`
- 意味: `meanings[].definitions[].definition`
- 例文: `meanings[].definitions[].example`

**レスポンス例（sick）:**
```json
[{
  "word": "sick",
  "phonetics": [{"text": "/sɪk/"}],
  "meanings": [
    {
      "partOfSpeech": "adjective",
      "definitions": [
        {
          "definition": "affected by physical or mental illness",
          "example": "nursing very sick children"
        },
        {
          "definition": "excellent; amazing",
          "example": "That trick was sick!"
        }
      ]
    }
  ]
}]
```

### Gemini API整形処理

**既存の実装を活用:**
- `ReplyController.php` で使用しているGemini API統合をベースにする
- `config('services.gemini.api_key')` で既存のAPIキーを使用

**プロンプト設計:**
```
あなたは日本人英語学習者向けの単語帳アシスタントです。

英単語: {word}
文脈: {context}（あれば）

辞書データ:
{dictionary_api_response}

以下の形式でJSON出力してください：
{
  "part_of_speech": "日本語の品詞（名詞/動詞/形容詞など）",
  "pronunciation_katakana": "カタカナ読み",
  "meanings": ["意味1", "意味2"],
  "en_example": "短く自然な会話例文",
  "jp_example": "自然な日本語訳",
  "context_meaning": "文脈での意味（文脈がある場合のみ）"
}

重要:
- 文脈がある場合、その文脈での意味を優先し、context_meaningに記載
- 意味は日本人学習者向けに自然な日本語で（辞書的すぎない）
- 例文は短く会話的に（InstagramやLINEで使えそうな感じ）
- 難しい説明は避けて簡潔に
- 必ずJSON形式で出力（マークダウンコードブロックは不要）
```

**処理フロー:**
1. Free Dictionary API呼び出し
2. レスポンスをGeminiプロンプトに埋め込み
3. Gemini APIでJSON生成
4. JSONパース後、フロントエンドへ返却

**エラー時のフォールバック:**
- 辞書APIが404の場合 → Geminiのみで生成
- Gemini JSONパースエラー → テキストレスポンスから抽出試行
- 両方失敗 → エラーメッセージを返却、手動入力にフォールバック

## UI/UX設計

### 新規単語登録フォーム

**現在のフォームを改修:**

```
┌──────────────────────────────────────────┐
│ 新しい単語を追加                           │
├──────────────────────────────────────────┤
│ 英単語 *                                  │
│ [入力フィールド]                           │
│                                          │
│ 文脈（オプション）                         │
│ [That was sick. など]                     │
│                                          │
│ [💡 AIで補完する] ← 新規追加              │
├──────────────────────────────────────────┤
│ ↓ 補完結果（全て編集可能）                 │
├──────────────────────────────────────────┤
│ 品詞                                      │
│ [形容詞 ▼]                                │
│                                          │
│ 発音記号                                  │
│ [/sɪk/]                                  │
│                                          │
│ カタカナ読み                               │
│ [シック]                                  │
│                                          │
│ 意味 1 *                                  │
│ [病気の]                                  │
│                                          │
│ 意味 2                                    │
│ [最高の（スラング）] [×削除]               │
│                                          │
│ [+ 意味を追加]                            │
│                                          │
│ 例文（英語）                               │
│ [That game was sick!]                    │
│                                          │
│ 例文（日本語）                             │
│ [あのゲーム最高だったね！]                  │
│                                          │
│ [✓ 登録する]                              │
└──────────────────────────────────────────┘
```

**UX詳細:**

1. **初期状態:**
   - 英単語と文脈フィールドのみ表示
   - 「AIで補完する」ボタンが表示

2. **補完ボタン押下時:**
   - ローディングスピナー表示
   - 「辞書データを取得中...」表示
   - 「AIで整形中...」表示
   - 2-3秒後にプレビューエリアが展開

3. **プレビュー表示後:**
   - 全フィールドが編集可能な状態で表示
   - 意味は複数追加・削除可能
   - 既存の「意味を追加」ボタンを活用

4. **エラー時:**
   - 「辞書に見つかりませんでした。AIが生成します...」
   - 「自動補完に失敗しました。手動で入力してください。」
   - エラー時も手動入力フォームは利用可能

5. **既存フローとの共存:**
   - 補完ボタンを押さなければ従来通り手動入力可能
   - 補完後も全フィールドを自由に編集可能

### 既存単語編集機能

**単語一覧画面（index.blade.php）に追加:**

```html
<!-- 既存の削除ボタンの隣に編集ボタンを追加 -->
<div class="flex items-start justify-between">
  <h2>{{$word["word"]}}</h2>
  <div class="flex gap-2">
    <a href="{{route('EditWord', $word['id'])}}"
       class="text-primary-400 hover:text-accent-600">
      <!-- 編集アイコン -->
    </a>
    <form method="post" action="{{route('DeleteWord')}}">
      <!-- 削除ボタン -->
    </form>
  </div>
</div>
```

**編集画面:**
- 新規登録フォームと同じレイアウト
- 英単語は読み取り専用（変更不可）
- 既存値をプリフィル
- 「AIで補完する」ボタンで不足情報を補完可能
- 更新ボタンとキャンセルボタン

### 単語一覧での表示改善

**品詞・発音の表示:**

```html
<!-- Before -->
<h2>sick</h2>
<p>・病気の</p>

<!-- After -->
<h2 class="text-2xl font-bold">
  sick
  @if($word->part_of_speech)
    <span class="text-sm text-primary-600 font-normal">
      ({{$word->part_of_speech}})
    </span>
  @endif
</h2>

@if($word->pronunciation || $word->pronunciation_katakana)
  <p class="text-sm text-primary-500 mb-2">
    🔊
    @if($word->pronunciation)
      {{$word->pronunciation}}
    @endif
    @if($word->pronunciation_katakana)
      ({{$word->pronunciation_katakana}})
    @endif
  </p>
@endif

<div class="border-t border-primary-100 pt-4 mb-5">
  <!-- 意味のリスト -->
</div>
```

## エラーハンドリング

### エラーケース別対応

**1. Free Dictionary APIエラー:**
- 404（単語が見つからない）
  - → Geminiのみで生成を試みる
  - ユーザーへの通知: 「辞書に見つかりませんでしたが、AIが生成します...」

- タイムアウト（30秒）
  - → Geminiのみで生成

- レート制限
  - → エラー通知「アクセスが集中しています。しばらく待ってから再試行してください。」

**2. Gemini APIエラー:**
- タイムアウト
  - → 「自動補完に失敗しました。手動で入力するか、後で再試行してください。」

- JSON形式エラー
  - → テキストからの抽出を試行
  - 失敗時は手動入力にフォールバック

- APIキー未設定
  - → 「Gemini APIキーが設定されていません。.envファイルを確認してください。」

**3. フォールバック階層:**
```
辞書API + Gemini（最優先）
  ↓ 辞書APIエラー
Geminiのみ（代替）
  ↓ Geminiエラー
手動入力フォーム（最終）
```

### ユーザーへのフィードバック

**ローディング状態:**
```javascript
// 補完ボタン押下時
「💡 AIで補完する」
  ↓
「⏳ 辞書データを取得中...」（1-2秒）
  ↓
「🤖 AIで整形中...」（1-2秒）
  ↓
「✅ 補完完了」→ プレビュー表示
```

**エラー表示:**
- Tailwindの既存アラートスタイルを使用
- エラー時も画面は破壊せず、手動入力可能な状態を維持

## テスト戦略

### 単体テスト

**WordAutoCompleteControllerTest.php:**
1. Free Dictionary APIレスポンスのパース
2. Gemini APIレスポンスのパース（JSON）
3. 辞書APIエラー時のフォールバック
4. Gemini APIエラー時のフォールバック
5. 文脈なしパターン
6. 文脈ありパターン

### 手動テスト項目

**一般的な単語:**
- "book", "happy", "run"
- → 辞書API + Gemini整形が正常動作

**スラング・口語表現:**
- "sick", "lit", "vibe"
- → 文脈考慮で適切な意味を優先

**文脈ありパターン:**
- 英単語: "sick", 文脈: "That was sick."
- → "最高の"という意味が優先表示

**辞書にない単語:**
- 造語、最新スラング
- → Geminiのみで生成

**既存単語の編集:**
- nullカラムを持つ既存単語を編集
- → 補完で情報追加が可能

**APIエラー:**
- ネットワーク切断時の動作
- タイムアウト時の動作
- → 適切なエラーメッセージとフォールバック

## 実装範囲

### Phase 1: 基本機能（必須）

1. **データベース**
   - マイグレーションファイル作成
   - マイグレーション実行
   - Wordモデルの$fillable更新

2. **バックエンド**
   - WordAutoCompleteController作成
   - autocompleteメソッド実装（辞書API + Gemini API）
   - ルート追加

3. **フロントエンド（新規登録）**
   - index.blade.phpに補完ボタン追加
   - JavaScriptで補完処理実装
   - プレビューエリア実装
   - ローディング表示実装

### Phase 2: 既存単語対応（必須）

4. **編集機能**
   - EditWordコントローラーメソッド追加
   - UpdateWordコントローラーメソッド追加
   - 編集画面作成（edit.blade.php）
   - ルート追加

5. **一覧画面改善**
   - index.blade.phpに編集ボタン追加
   - 品詞・発音の表示追加

### Phase 3: UI改善（必須）

6. **ユーザー体験**
   - ローディングスピナー
   - エラーメッセージ表示
   - レスポンシブ対応確認

### Phase 4: オプション（余裕があれば）

- 一括補完機能（既存単語全てに対して補完）
- 補完履歴の保存
- お気に入りの意味をハイライト

## 技術スタック

- **フレームワーク:** Laravel 11
- **フロントエンド:** Blade + vanilla JavaScript
- **CSSフレームワーク:** Tailwind CSS（既存）
- **外部API:**
  - Free Dictionary API（無料、認証不要）
  - Gemini API（gemini-3-flash-preview）
- **HTTPクライアント:** Laravel Http（既存）
- **新規依存パッケージ:** なし

## 成功基準

1. ✅ 英単語入力 → 補完ボタン → 2-3秒以内にプレビュー表示
2. ✅ 文脈を考慮した意味が優先表示される
3. ✅ 既存の手動入力フローを破壊しない
4. ✅ 既存単語も編集で補完可能
5. ✅ nullカラムを許容し、既存データとの互換性を保つ
6. ✅ エラー時も手動入力でリカバリー可能

## セキュリティ考慮事項

- Gemini APIキーは`.env`で管理（既存と同様）
- ユーザー入力のバリデーション実装
- XSS対策（Bladeの`{{}}`エスケープを活用）
- CSRF保護（Laravel標準機能）

## 参考情報

**Free Dictionary API:**
- ドキュメント: https://dictionaryapi.dev/
- レート制限: 450リクエスト/5分（無料）
- 認証: 不要

**Gemini API:**
- 既存の実装: `app/Http/Controllers/ReplyController.php:82-93`
- モデル: gemini-3-flash-preview
- APIキー: `config('services.gemini.api_key')`
