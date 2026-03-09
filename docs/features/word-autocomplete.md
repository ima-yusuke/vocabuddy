# 英単語自動補完機能

## 概要

英単語の情報（発音、品詞、意味、例文）を自動で取得し、単語登録を効率化する機能です。Free Dictionary APIとGoogle Gemini AIを組み合わせることで、正確かつ日本人学習者にとってわかりやすい情報を提供します。

## 機能詳細

### 1. 自動補完の仕組み

この機能は2段階のプロセスで動作します：

**ステップ1: 辞書APIから基本情報を取得**
- [Free Dictionary API](https://dictionaryapi.dev/)を使用
- 発音記号、品詞、定義などの正確な辞書データを取得
- ネットワークエラーや単語未登録の場合でも次のステップに進む

**ステップ2: Gemini AIで日本語化・最適化**
- 辞書データを元に、日本人学習者向けに最適化
- 自然な日本語訳を生成（辞書的すぎない表現）
- 文脈がある場合は、その文脈での意味を優先
- 短く会話的な例文を生成

### 2. 使い方

#### 新規単語登録

1. トップページの「新しい単語を追加」フォームにアクセス
2. 「英単語」フィールドに単語を入力（例: "match"）
3. （任意）「文脈・例文」フィールドに文脈を入力（例: "That was sick"）
4. 「AIで意味を補完」ボタンをクリック
5. AIが情報を取得中（10-30秒）
6. 「AI補完結果」セクションに以下の情報が表示される：
   - 品詞（例: 名詞、動詞）
   - 発音記号（例: /mætʃ/）
   - 発音（カタカナ）（例: マッチ）
   - 意味（複数の意味が表示され、編集可能）
   - 例文（英語・日本語）
7. 必要に応じて情報を編集
   - 意味の追加・削除が可能
   - 例文の修正が可能
8. 「登録する」ボタンで保存

#### 既存単語編集

1. 単語一覧から編集したい単語の編集アイコンをクリック
2. 編集ページで「英単語」フィールドを確認
3. （任意）新しい文脈を入力して意味を取り直す
4. 「AIで意味を補完」ボタンをクリック
5. 新しい補完結果を確認・編集
6. 「更新する」ボタンで保存

### 3. 対応フィールド

自動補完機能は以下のフィールドに対応しています：

| フィールド名 | 説明 | データベースカラム |
|------------|------|------------------|
| 品詞 | 名詞/動詞/形容詞など | `part_of_speech` |
| 発音記号 | IPA形式の発音記号 | `pronunciation` |
| 発音（カタカナ） | 日本人向けのカタカナ読み | `pronunciation_katakana` |
| 意味 | 日本語の意味（複数可） | `japanese` テーブル |
| 例文（英語） | 会話的な英語例文 | `en_example` |
| 例文（日本語） | 自然な日本語訳 | `jp_example` |

**注意**: 既存の単語データ（補完機能実装前に登録されたもの）には、品詞・発音のデータがない場合があります。これらの単語も問題なく表示されます（後方互換性あり）。

### 4. 文脈対応

文脈を入力することで、より適切な意味を取得できます。

**例1: "sick" という単語**
- 文脈なし → "病気の" という一般的な意味
- 文脈あり（"That was sick"） → "すごい、かっこいい" というスラング的な意味

**例2: "match" という単語**
- 文脈なし → "試合、マッチ、合う" など複数の意味
- 文脈あり（"The colors match"） → "色が合う" という意味を優先

AIはcontext_meaningフィールドを使用して、文脈での意味を抽出します。

## 技術仕様

### API

**Free Dictionary API**
- エンドポイント: `https://api.dictionaryapi.dev/api/v2/entries/en/{word}`
- メソッド: GET
- タイムアウト: 10秒
- 無料・認証不要

**Gemini AI API**
- モデル: `gemini-3-flash-preview`
- エンドポイント: `https://generativelanguage.googleapis.com/v1beta/models/gemini-3-flash-preview:generateContent`
- メソッド: POST
- タイムアウト: 30秒
- APIキー: `.env`の`GEMINI_API_KEY`で設定

### データベース

**マイグレーション**: `2026_03_09_063934_add_pronunciation_and_pos_to_words_table.php`

追加されたカラム（`words`テーブル）:

```php
$table->string('part_of_speech')->nullable()->after('word');
$table->string('pronunciation')->nullable()->after('part_of_speech');
$table->string('pronunciation_katakana')->nullable()->after('pronunciation');
```

すべてのカラムは`nullable`のため、既存データとの互換性があります。

### エンドポイント

**自動補完API**
- ルート名: `AutocompleteWord`
- URL: `POST /word/autocomplete`
- コントローラー: `WordAutoCompleteController@autocomplete`
- リクエストボディ:
  ```json
  {
    "word": "英単語（必須、最大100文字）",
    "context": "文脈（任意、最大500文字）"
  }
  ```
- レスポンス（成功時）:
  ```json
  {
    "success": true,
    "data": {
      "part_of_speech": "名詞",
      "pronunciation": "/mætʃ/",
      "pronunciation_katakana": "マッチ",
      "meanings": ["試合", "マッチ", "合う"],
      "en_example": "The match was exciting!",
      "jp_example": "その試合はエキサイティングだった！",
      "context_meaning": "文脈での意味（文脈がある場合のみ）"
    }
  }
  ```
- レスポンス（エラー時）:
  ```json
  {
    "success": false,
    "error": "エラーメッセージ",
    "error_type": "api_key_missing | timeout | parse_error | general"
  }
  ```

**単語登録**
- ルート名: `AddWord`
- URL: `POST /`
- コントローラー: `MainController@AddWord`
- 新規フィールド: `part_of_speech`, `pronunciation`, `pronunciation_katakana`

**単語更新**
- ルート名: `UpdateWord`
- URL: `PATCH /word/update/{id}`
- コントローラー: `MainController@UpdateWord`
- 新規フィールド: `part_of_speech`, `pronunciation`, `pronunciation_katakana`

## エラーハンドリング

### エラーの種類と対処

1. **APIキー未設定** (`api_key_missing`)
   - 原因: `.env`に`GEMINI_API_KEY`が設定されていない
   - 表示メッセージ: "APIキーが設定されていません"
   - ユーザー対処: 管理者に問い合わせ
   - 開発者対処: `.env`に正しいAPIキーを設定

2. **タイムアウトエラー** (`timeout`)
   - 原因: ネットワーク遅延、APIサーバー応答遅延
   - 表示メッセージ: "APIリクエストがタイムアウトしました"
   - ユーザー対処: しばらく待ってから再試行
   - フォールバック: 手動入力フォームを表示

3. **パースエラー** (`parse_error`)
   - 原因: AIの応答が期待したJSON形式でない
   - 表示メッセージ: "AIの応答を解析できませんでした"
   - ユーザー対処: 手動で意味を入力
   - ログ: 詳細なエラー情報を記録

4. **一般的なエラー** (`general`)
   - 原因: その他の予期しないエラー
   - 表示メッセージ: エラーの内容に応じた具体的なメッセージ
   - ユーザー対処: 文脈追加、または手動入力
   - フォールバック: 手動入力フォームを表示

### フォールバック機能

エラーが発生した場合でも、ユーザーは手動で情報を入力できます：
- エラーメッセージがアラートで表示される
- 手動入力フォームが自動的に表示される
- 既に入力した英単語・文脈はそのまま保持される

### ロギング

すべてのリクエストとエラーは`storage/logs/laravel.log`に記録されます：

```php
// 成功時
Log::info('Autocomplete successful', ['word' => $word]);

// エラー時
Log::error('Autocomplete error', [
    'word' => $word,
    'has_context' => !is_null($context),
    'error' => $e->getMessage(),
    'trace' => $e->getTraceAsString()
]);
```

## UI/UX設計

### ローディング状態

1. ユーザーが「AIで意味を補完」をクリック
2. ローディングメッセージ表示: "辞書から単語情報を取得しています..."
3. Dictionary API完了後: "AIが日本語訳を生成しています..."
4. 完了後: AI補完結果を表示

### 編集可能なプレビュー

- 補完結果は編集可能な入力フィールドとして表示
- 意味の追加・削除ボタンあり
- すぐに登録せず、確認・編集できる
- 例文も自由に修正可能

### 後方互換性

- 品詞・発音データがない既存単語も正常に表示
- 単語一覧で条件分岐により、データがある場合のみ表示
  ```blade
  @if($word["part_of_speech"])
      <span>{{$word["part_of_speech"]}}</span>
  @endif
  ```

## 今後の改善案

1. **発音音声の追加**
   - Free Dictionary APIは音声URLも提供している
   - 音声再生ボタンを追加し、ネイティブ発音を聞ける機能

2. **キャッシュ機能**
   - 同じ単語の補完結果をキャッシュ
   - API呼び出し回数を削減し、レスポンス速度向上

3. **バッチ補完**
   - 既存の単語データ（発音・品詞なし）を一括補完
   - Artisanコマンドで実行

4. **辞書の選択肢追加**
   - 他の辞書API（Oxford Dictionary API等）の統合
   - より高度な定義や例文の取得

5. **オフライン対応**
   - ローカル辞書データベースの構築
   - ネットワークなしでも基本的な補完が可能

6. **品詞フィルター**
   - 単語一覧で品詞でフィルタリング
   - 名詞だけ、動詞だけなど絞り込み検索

7. **発音練習機能**
   - 音声認識APIを使用
   - ユーザーの発音を評価

## トラブルシューティング

### Q: 補完ボタンを押しても何も起きない
A: ブラウザのコンソールでJavaScriptエラーを確認してください。CSRF tokenが正しく設定されているか確認してください。

### Q: "APIキーが設定されていません"エラー
A: `.env`ファイルに以下の行を追加してください：
```
GEMINI_API_KEY=your_actual_api_key_here
```
その後、`php artisan config:clear`を実行してください。

### Q: タイムアウトエラーが頻発する
A: ネットワーク接続を確認してください。Gemini APIの無料枠の制限に達している可能性もあります。

### Q: 古い単語に発音・品詞が表示されない
A: これは正常な動作です。補完機能実装前の単語にはこれらのデータがありません。編集ページで「AIで意味を補完」を実行すれば追加できます。

### Q: AIが変な意味を返してくる
A: 文脈を追加すると精度が上がります。または、補完結果を手動で編集してから保存してください。

## 参考資料

- [Free Dictionary API Documentation](https://dictionaryapi.dev/)
- [Google Gemini API Documentation](https://ai.google.dev/docs)
- [Laravel HTTP Client Documentation](https://laravel.com/docs/11.x/http-client)
