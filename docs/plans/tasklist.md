# VocaBuddy タスクリスト

最終更新: 2026-03-26

## 進捗サマリー
- **完了**: 0/38
- **進行中**: 0/38
- **未着手**: 38/38

---

## 🎨 優先度1: 全ページデザイン統一（10タスク）

### [ ] 1. 全ページのフォントをZen Kaku Gothic Newに統一
- 対象: 全bladeファイル
- Google Fonts読み込み追加
- font-familyを統一

### [ ] 2. 全ページのカラーを黄色・黒・白に統一（claude.md準拠）
- メインカラー: #ffeb54（黄色）
- 黒: #1A1A1A
- 白: #FFFFFF
- グラデーションや他の色を削除

### [ ] 3. words/index.blade.phpのデザイン変更
- 背景色を黄色・白に変更
- ボタンを黄色背景+黒テキストに
- カードのボーダーを黒に

### [ ] 4. edit-word.blade.phpのデザイン変更
- 同上

### [ ] 5. test-start.blade.phpのデザイン変更
- 同上

### [ ] 6. test.blade.phpのデザイン変更
- 同上

### [ ] 7. test-result.blade.phpのデザイン変更
- 同上

### [ ] 8. reply-assistant.blade.phpのデザイン変更
- 同上

### [ ] 9. reply-result.blade.phpのデザイン変更
- 同上

### [ ] 10. side-menu.blade.phpのデザイン変更
- 背景を黒または黄色に
- テキストを白または黒に

---

## 💰 優先度2: 料金プラン設定更新（2タスク）

### [ ] 11. ドキュメント通りにプラン設定を変更
- Free: 50単語、AI返信2回/日
- Basic: 300単語、AI返信10回/日、¥300-500
- Pro: 無制限単語、AI返信月300回、¥800-1200
- Premium: 無制限、AIほぼ無制限、¥1500-2000
- LandingController.phpとPricingController.phpを更新

### [ ] 12. ランディングページの料金表を更新
- landing.blade.phpの料金セクションを更新
- 新しいプラン名（Basic, Pro, Premium）に変更
- AI返信回数の記載を追加

---

## 🤖 優先度3: AI返信機能強化（7タスク）

### [ ] 13. reply_templatesテーブルを作成
```sql
- id
- user_id
- category (friend/lover/work)
- partner_message (相手の英文)
- intent_ja (自分の意図・日本語)
- reply_en (生成された英文)
- reply_ja (日本語訳)
- vocab_ids (使用単語ID, JSON)
- times_used (使用回数)
- embedding (Gemini Embeddings用)
- created_at, updated_at
```

### [ ] 14. reply_usage_logsテーブルを作成
```sql
- id
- template_id
- used_at
```

### [ ] 15. ReplyTemplateモデルを作成
- Userとのリレーション
- vocab_idsのキャスト設定
- times_usedの自動インクリメント

### [ ] 16. 返信アシスタントに履歴保存機能を追加
- ReplyController@GenerateReplyに保存処理追加
- 生成後に自動保存
- embeddingも同時保存

### [ ] 17. Gemini Embeddings APIで返信文の類似検索機能を実装
- Gemini text-embedding-004を使用
- コサイン類似度で検索
- 閾値0.8以上を類似と判定

### [ ] 18. 返信作成前に類似返信を表示する機能を追加
- 入力後、AI生成前に類似返信を検索
- 「過去に似た返信があります」と表示
- 選択 or 新規生成を選べるUI

### [ ] 19. 返信履歴一覧ページを作成
- /reply-history
- カテゴリ別フィルター
- 使用回数でソート
- 再利用ボタン

---

## 💾 優先度4: Phase 2 - データベース実装（6タスク）

### [ ] 20. plansテーブルを作成
```sql
- id
- name (Free/Basic/Pro/Premium)
- slug (free/basic/pro/premium)
- word_limit (50/300/null/null)
- ai_reply_daily_limit (2/10/null/null)
- ai_reply_monthly_limit (null/null/300/null)
- price_monthly (0/500/1200/2000)
- price_yearly (0/5000/12000/20000)
- ai_model (flash/flash/pro/pro)
- is_active
- created_at, updated_at
```

### [ ] 21. subscriptionsテーブルを作成
```sql
- id
- user_id
- plan_id
- status (active/cancelled/expired)
- stripe_subscription_id
- stripe_customer_id
- started_at
- ends_at
- created_at, updated_at
```

### [ ] 22. ai_usage_logsテーブルを作成
```sql
- id
- user_id
- type (reply/autocomplete/test)
- tokens_used
- model_used
- created_at
```

### [ ] 23. Plan, Subscriptionモデルを作成
- Planモデル: スコープでアクティブプラン取得
- Subscriptionモデル: Userとのリレーション、有効期限チェック

### [ ] 24. プランごとの単語登録数制限をミドルウェアで実装
- CheckWordLimitミドルウェア作成
- 単語登録時にプランの制限をチェック
- 超過時はエラーメッセージ表示

### [ ] 25. プランごとのAI返信回数制限をミドルウェアで実装
- CheckAiUsageLimitミドルウェア作成
- 日次/月次の制限をチェック
- 超過時はアップグレード誘導

---

## 📝 優先度5: 単語テスト改善（5タスク）

### [ ] 26. 単語テストに問題数選択機能を追加（10/20/30問）
- test-start.blade.phpに選択UI追加
- TestControllerで問題数を動的に変更

### [ ] 27. AIで誤答選択肢を生成する機能を追加
- 正解に近い単語をGeminiで生成
- より自然な誤答を作成

### [ ] 28. 苦手単語を記録するテーブルを作成
```sql
- id
- user_id
- word_id
- incorrect_count
- last_incorrect_at
- created_at, updated_at
```

### [ ] 29. 苦手単語優先出題機能を実装
- incorrect_countが高い単語を優先
- 一定期間経過した単語を復習

### [ ] 30. 単語の使用頻度を記録する機能を実装
- reply_templatesのvocab_idsから集計
- よく使う単語を可視化

---

## ⚡ 優先度6: AIコスト最適化（2タスク）

### [ ] 31. プラン別AIモデル切り替え機能を実装
- Free/Basic: gemini-2.0-flash-exp
- Pro/Premium: gemini-2.0-flash-thinking-exp-1219
- プランに応じて自動切り替え

### [ ] 32. AI返信結果のキャッシュ機能を実装
- 同じ入力は24時間キャッシュ
- Laravelのキャッシュシステム使用
- コスト削減

---

## 💳 優先度7: Phase 3 - Stripe決済（6タスク）

### [ ] 33. Stripe APIキーを設定
- .envにSTRIPE_KEYとSTRIPE_SECRETを追加
- Laravel Cashier導入

### [ ] 34. Stripeに料金プランを作成
- Basic（月額¥500、年額¥5000）
- Pro（月額¥1200、年額¥12000）
- Premium（月額¥2000、年額¥20000）

### [ ] 35. Stripe Checkoutセッション作成機能を実装
- プラン選択からCheckout画面へ遷移
- 成功/キャンセル時のリダイレクト処理

### [ ] 36. Stripe Webhookを実装
- checkout.session.completed
- customer.subscription.updated
- customer.subscription.deleted
- subscriptionsテーブルを自動更新

### [ ] 37. サブスクリプション管理ページを作成
- 現在のプラン表示
- 使用状況（単語数、AI回数）表示
- プラン変更ボタン

### [ ] 38. プランアップグレード/ダウングレード機能を実装
- Stripe APIで即座に変更
- 日割り計算
- ダウングレード時の制限処理

---

## メモ
- 決済機能は最後に実装
- AI機能とDB実装を優先
- デザイン統一を最優先で完了させる
