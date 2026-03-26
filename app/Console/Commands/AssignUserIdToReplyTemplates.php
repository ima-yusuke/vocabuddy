<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ReplyTemplate;
use App\Models\User;

class AssignUserIdToReplyTemplates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'replies:assign-user {user_id : The user ID to assign to reply templates}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign user_id to reply templates that don\'t have one';

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

        // 現在の状況を表示
        $totalTemplates = ReplyTemplate::count();
        $templatesWithoutUser = ReplyTemplate::whereNull('user_id')->count();

        $this->info("=== Current Status ===");
        $this->info("User: {$user->email} (ID: {$user->id})");
        $this->info("Total reply templates: {$totalTemplates}");
        $this->info("Reply templates without user_id: {$templatesWithoutUser}");
        $this->newLine();

        if ($templatesWithoutUser === 0) {
            $this->info("All reply templates already have user_id assigned!");
            return 0;
        }

        // 確認プロンプト
        if (!$this->confirm("Assign user_id={$userId} to {$templatesWithoutUser} reply templates?")) {
            $this->info("Operation cancelled.");
            return 0;
        }

        // 実行
        $updated = ReplyTemplate::whereNull('user_id')->update(['user_id' => $userId]);

        $this->newLine();
        $this->info("✅ Successfully updated {$updated} reply templates with user_id: {$userId}");

        return 0;
    }
}
