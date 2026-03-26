<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeakWord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'word_id',
        'incorrect_count',
        'consecutive_correct_count',
        'last_incorrect_at',
    ];

    protected $casts = [
        'last_incorrect_at' => 'datetime',
    ];

    /**
     * この苦手単語を持つユーザー
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 苦手単語の詳細
     */
    public function word(): BelongsTo
    {
        return $this->belongsTo(Word::class);
    }

    /**
     * 不正解を記録（連続正解をリセット）
     */
    public function recordIncorrect(): void
    {
        $this->increment('incorrect_count');
        $this->update([
            'consecutive_correct_count' => 0,
            'last_incorrect_at' => now(),
        ]);
    }

    /**
     * 正解を記録（連続正解をカウント）
     * 3回連続正解したらレコード削除（苦手克服）
     */
    public function recordCorrect(): void
    {
        $this->increment('consecutive_correct_count');

        // 3回連続正解で苦手から除外
        if ($this->consecutive_correct_count >= 3) {
            $this->delete();
        }
    }
}
