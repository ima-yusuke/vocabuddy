<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Word extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'word',
        'part_of_speech',
        'pronunciation_katakana',
    ];

    /**
     * この単語を登録したユーザー
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function japanese(): HasMany
    {
        return $this->hasMany(Japanese::class);
    }

    /**
     * この単語の使用頻度を取得（AI返信で何回使われたか）
     */
    public function usageCount(): int
    {
        return ReplyTemplate::where('user_id', $this->user_id)
            ->whereJsonContains('vocab_ids', $this->id)
            ->count();
    }

    /**
     * この単語を使用したAI返信テンプレート
     */
    public function replyTemplates()
    {
        return ReplyTemplate::where('user_id', $this->user_id)
            ->whereJsonContains('vocab_ids', $this->id)
            ->get();
    }

}
