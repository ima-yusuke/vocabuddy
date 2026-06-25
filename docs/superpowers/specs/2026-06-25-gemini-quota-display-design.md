# Gemini無料枠 残量表示 設計ドキュメント

## 背景・課題

単語帳のAI単語調べ（autocomplete）で「AIサービスが一時的に利用できません／予期しないエラー」が断続的に発生。「他の単語なら動く」現象があった。

調査の結果、根本原因は **Gemini APIの無料枠レート制限**と判明（本番ログの429エラーで確認）:

```
Quota exceeded for metric: generate_content_free_tier_requests,
limit: 20, model: gemini-2.5-flash
"GenerateRequestsPerDayPerProjectPerModel-FreeTier", quotaValue: "20"
Please retry in 45.301673539s.
```

単語のスペルではなく、**リクエストの頻度・回数**が原因。短時間に呼びすぎると一時ブロックされ、少し待つと回復する。

## ゴール

単語調べ（`gemini-2.5-flash`）の無料枠について、**毎分・1日あたりの残り使用回数**と、上限到達時の**回復までの時間**を画面に表示する。

## スコープ

### 対象
- モデル `gemini-2.5-flash`（= 単語調べ autocomplete）の残量のみ表示

### 対象外（YAGNI）
- reply / test / embedding（別モデル `gemini-3-flash-preview` 等）の残量
- 自動リトライ、有料化、provider差し替え
- test/embeddingのAiUsageLog記録漏れ修正（今回は不要 — autocompleteのみ集計するため）

## クォータの実態

- Geminiの無料枠クォータは**モデルごとに独立**。
- RPD（1日）= **20**（本番ログで確定）
- RPM（毎分）= 公開値非公開のため**設定値として持つ**（初期値10、実測で補正）

## データソース

既存の `ai_usage_logs` テーブルを集計（マイグレーション不要）:
- `type = 'autocomplete'`
- `model_used = 'gemini-2.5-flash'`
- `created_at` で時間窓を絞る

autocompleteは成功時に既に `AiUsageLog::create` で記録済み。**記録追加は不要。**

## アーキテクチャ

3つのユニットに分割:

1. **クォータ設定** (`config/services.php`) — RPM/RPDの上限値を保持。1箇所で調整可能。
2. **集計サービス** (`app/Services/GeminiQuotaService.php`) — `ai_usage_logs` を読み、残量・回復時刻を計算する読み取り専用ロジック。テスト可能な純粋メソッド。
3. **表示** — エンドポイント `GET /word/ai-quota`（`WordAutoCompleteController::quota`）がJSONを返し、単語調べUIのJSがそれを描画。

既存の `autocomplete` メソッド・`fetchDictionaryData`・`formatWithAI` には手を入れない（残量表示は独立した読み取り専用機能）。

## 集計ロジック詳細

`GeminiQuotaService`:

- **毎分残量** = `flash_rpm` − （直近60秒のautocompleteログ件数）。下限0。
- **1日残量** = `flash_rpd` − （今日のautocompleteログ件数）。下限0。
- **毎分の回復秒数** = 毎分残量が0のとき、直近60秒で最古のログの `created_at + 60秒` までの秒数。それ以外は0。
- **1日の回復時刻** = 1日残量が0のとき、翌日0:00（アプリのタイムゾーン）。それ以外はnull。

集計は現状「全ユーザー横断（user_id絞りなし）」とする。理由: Geminiの無料枠はAPIキー単位で全ユーザー共有。現状は単独利用なので結果は同じだが、将来公開時に変更不要。

## 表示仕様

- 単語調べボタン（`#autocomplete_btn`）の下に残量表示エリアを追加。
- ページ表示時とautocomplete実行後に `GET /word/ai-quota` を叩いて更新。
- 表示文言例: `今日: 残り 18/20 回 ・ 直近1分: 残り 8/10 回`
- 毎分残量が0のとき: `あと {秒数} 秒で回復します` をカウントダウン表示。
- 1日残量が0のとき: `本日の上限に達しました（翌日0時に回復）`。

## 既知の限界（正直に明記）

- 失敗リクエスト（429含む）は `AiUsageLog` に記録されないため、Googleの実カウントよりやや甘め。安全側にRPMを低め(10)に設定して緩和。
- RPMの実値はGoogle非公開・変更され得る。設定値で対応し、429が出た実績から補正する運用。
- 1日リセットはGeminiが太平洋時間の可能性。まずアプリTZ翌日0時で実装し、ズレたら補正。

## テスト方針

`GeminiQuotaService` をPHPUnitでテスト（DBに `AiUsageLog` を作って集計結果を検証）。時間依存は `Carbon::setTestNow()` で固定。
