# 本番環境セットアップ手順

## 1. マイグレーション実行

### wordsテーブルにuser_id追加
```bash
sail artisan migrate
```

## 2. プランデータの作成/更新

### 全プランを作成（Free/Basic/Pro/Premium/Admin）
```bash
sail artisan db:seed --class=PlanSeeder
```

## 3. 既存データのuser_id割り当て

### Step 3.1: 単語にuser_idを割り当て
```bash
# ユーザーIDを確認（通常は1）
sail artisan words:assign-user 1
```

このコマンドは：
- 現在の単語数を表示
- user_idがない単語数を表示
- 確認プロンプトを表示
- 実行して結果を表示

### Step 3.2: AI返信文にuser_idを割り当て（必要な場合）
```bash
# 必要な場合のみ実行
sail artisan replies:assign-user 1
```

## 4. Adminプランを自分に割り当て

### 簡単な方法：コマンド1つで完了
```bash
sail artisan plan:assign-admin 1
```

このコマンドは：
- 現在のプランを表示
- Adminプランの詳細を表示
- 確認プロンプトを表示
- 既存のサブスクリプションを削除
- Adminプランを割り当て

## 5. 確認

### 現在のプランと単語数を確認
```bash
sail artisan tinker
```

Tinkerで以下を実行：
```php
$user = \App\Models\User::find(1);
$plan = $user->currentPlan();
echo "Current plan: {$plan->name}\n";
echo "Your words: " . $user->words()->count() . "\n";
echo "Words without user_id: " . \App\Models\Word::whereNull('user_id')->count() . "\n";
exit
```

## 完了！

これで以下が実現されます：
- ✅ 全ての単語があなたに紐づく
- ✅ 全てのAI返信文があなたに紐づく
- ✅ Adminプラン（無制限）が割り当てられる
- ✅ 単語登録・AI使用が無制限になる

## トラブルシューティング

### マイグレーションエラーが出る場合
```bash
# マイグレーション状態を確認
sail artisan migrate:status

# 特定のマイグレーションだけ実行
sail artisan migrate --path=database/migrations/2026_03_26_084944_add_user_id_to_words_table.php
```

### キャッシュクリア
```bash
sail artisan cache:clear
sail artisan config:clear
sail artisan route:clear
sail artisan view:clear
```
