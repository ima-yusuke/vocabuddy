<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Word;
use App\Models\User;

class AssignUserIdToWords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'words:assign-user {user_id : The user ID to assign to words}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign user_id to words that don\'t have one';

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
        $totalWords = Word::count();
        $wordsWithoutUser = Word::whereNull('user_id')->count();

        $this->info("=== Current Status ===");
        $this->info("User: {$user->email} (ID: {$user->id})");
        $this->info("Total words: {$totalWords}");
        $this->info("Words without user_id: {$wordsWithoutUser}");
        $this->newLine();

        if ($wordsWithoutUser === 0) {
            $this->info("All words already have user_id assigned!");
            return 0;
        }

        // 確認プロンプト
        if (!$this->confirm("Assign user_id={$userId} to {$wordsWithoutUser} words?")) {
            $this->info("Operation cancelled.");
            return 0;
        }

        // 実行
        $updated = Word::whereNull('user_id')->update(['user_id' => $userId]);

        $this->newLine();
        $this->info("✅ Successfully updated {$updated} words with user_id: {$userId}");

        return 0;
    }
}
