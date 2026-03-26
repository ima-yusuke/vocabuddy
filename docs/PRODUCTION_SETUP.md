# 本番環境セットアップ手順

## 1. マイグレーション実行

### wordsテーブルにuser_id追加
```bash
php artisan migrate
```

## 2. プランデータの作成/更新

### 全プランを作成（Free/Basic/Pro/Premium/Admin）
```bash
php artisan db:seed --class=PlanSeeder
```

## 3. 既存データのuser_id割り当て

### Step 3.1: 現在のユーザーを確認
```bash
php artisan tinker --execute="
\$user = \App\Models\User::first();
echo 'Your user ID: ' . \$user->id . PHP_EOL;
echo 'Your email: ' . \$user->email . PHP_EOL;
"
```

### Step 3.2: 単語の状況を確認
```bash
php artisan tinker --execute="
echo 'Total words: ' . \App\Models\Word::count() . PHP_EOL;
echo 'Words without user_id: ' . \App\Models\Word::whereNull('user_id')->count() . PHP_EOL;
"
```

### Step 3.3: 全単語にuser_idを割り当て
```bash
# ユーザーIDを確認後、以下を実行（例: user_id = 1）
php artisan tinker --execute="
\$userId = 1; # あなたのユーザーIDに変更してください
\$count = \App\Models\Word::whereNull('user_id')->update(['user_id' => \$userId]);
echo 'Updated ' . \$count . ' words with user_id: ' . \$userId . PHP_EOL;
"
```

### Step 3.4: AI返信文の状況を確認
```bash
php artisan tinker --execute="
echo 'Total reply templates: ' . \App\Models\ReplyTemplate::count() . PHP_EOL;
echo 'Without user_id: ' . \App\Models\ReplyTemplate::whereNull('user_id')->count() . PHP_EOL;
"
```

### Step 3.5: （必要な場合）AI返信文にuser_idを割り当て
```bash
# user_idがないものがある場合のみ実行
php artisan tinker --execute="
\$userId = 1; # あなたのユーザーIDに変更してください
\$count = \App\Models\ReplyTemplate::whereNull('user_id')->update(['user_id' => \$userId]);
echo 'Updated ' . \$count . ' reply templates with user_id: ' . \$userId . PHP_EOL;
"
```

## 4. Adminプランを自分に割り当て

### Step 4.1: Adminプランを確認
```bash
php artisan tinker --execute="
\$adminPlan = \App\Models\Plan::where('slug', 'admin')->first();
echo 'Admin Plan ID: ' . \$adminPlan->id . PHP_EOL;
echo 'Admin Plan Name: ' . \$adminPlan->name . PHP_EOL;
"
```

### Step 4.2: 現在のサブスクリプションを削除（必要な場合）
```bash
php artisan tinker --execute="
\$userId = 1; # あなたのユーザーIDに変更してください
\$deleted = \App\Models\Subscription::where('user_id', \$userId)->delete();
echo 'Deleted ' . \$deleted . ' old subscriptions' . PHP_EOL;
"
```

### Step 4.3: Adminプランのサブスクリプションを作成
```bash
php artisan tinker --execute="
\$userId = 1; # あなたのユーザーIDに変更してください
\$adminPlan = \App\Models\Plan::where('slug', 'admin')->first();

\App\Models\Subscription::create([
    'user_id' => \$userId,
    'plan_id' => \$adminPlan->id,
    'status' => 'active',
    'started_at' => now(),
    'ends_at' => null,
]);

echo 'Admin subscription created!' . PHP_EOL;
"
```

## 5. 確認

### Step 5.1: プランを確認
```bash
php artisan tinker --execute="
\$user = \App\Models\User::find(1);
\$plan = \$user->currentPlan();
echo 'Your current plan: ' . \$plan->name . PHP_EOL;
echo 'Word limit: ' . (\$plan->word_limit ?? 'unlimited') . PHP_EOL;
echo 'AI daily limit: ' . (\$plan->ai_reply_daily_limit ?? 'unlimited') . PHP_EOL;
echo 'AI monthly limit: ' . (\$plan->ai_reply_monthly_limit ?? 'unlimited') . PHP_EOL;
"
```

### Step 5.2: 単語数を確認
```bash
php artisan tinker --execute="
\$user = \App\Models\User::find(1);
echo 'Your words: ' . \$user->words()->count() . PHP_EOL;
echo 'Words without user_id: ' . \App\Models\Word::whereNull('user_id')->count() . PHP_EOL;
"
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
php artisan migrate:status

# 特定のマイグレーションだけ実行
php artisan migrate --path=database/migrations/2026_03_26_084944_add_user_id_to_words_table.php
```

### キャッシュクリア
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```
