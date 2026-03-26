<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Plan;
use App\Models\Subscription;

class AssignAdminPlan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plan:assign-admin {user_id : The user ID to assign admin plan}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign Admin plan (unlimited) to a user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');

        // ユーザーが存在するか確認
        $user = User::find($userId);
        if (!$user) {
            $this->error("User with ID {$userId} not found!");
            return 1;
        }

        // Adminプランを取得
        $adminPlan = Plan::where('slug', 'admin')->first();
        if (!$adminPlan) {
            $this->error("Admin plan not found! Please run: sail artisan db:seed --class=PlanSeeder");
            return 1;
        }

        // 現在の状況を表示
        $currentPlan = $user->currentPlan();
        $this->info("=== Current Status ===");
        $this->info("User: {$user->email} (ID: {$user->id})");
        $this->info("Current plan: {$currentPlan->name}");
        $this->newLine();
        $this->info("=== Admin Plan Details ===");
        $this->info("Name: {$adminPlan->name}");
        $this->info("Word limit: unlimited");
        $this->info("AI daily limit: unlimited");
        $this->info("AI monthly limit: unlimited");
        $this->newLine();

        // 確認プロンプト
        if (!$this->confirm("Assign Admin plan to {$user->email}?")) {
            $this->info("Operation cancelled.");
            return 0;
        }

        // 既存のサブスクリプションを削除
        $deletedCount = Subscription::where('user_id', $userId)->delete();
        if ($deletedCount > 0) {
            $this->info("Deleted {$deletedCount} old subscription(s)");
        }

        // Adminプランのサブスクリプションを作成
        Subscription::create([
            'user_id' => $userId,
            'plan_id' => $adminPlan->id,
            'status' => 'active',
            'started_at' => now(),
            'ends_at' => null,
        ]);

        $this->newLine();
        $this->info("✅ Successfully assigned Admin plan to {$user->email}!");
        $this->info("   Word limit: unlimited");
        $this->info("   AI usage: unlimited");

        return 0;
    }
}
