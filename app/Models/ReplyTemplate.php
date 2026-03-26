<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReplyTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category',
        'partner_message',
        'intent_ja',
        'reply_en',
        'reply_ja',
        'vocab_ids',
        'times_used',
        'embedding',
    ];

    protected $casts = [
        'vocab_ids' => 'array',
        'embedding' => 'array',
        'times_used' => 'integer',
    ];

    /**
     * ユーザーとのリレーション
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 使用ログとのリレーション
     */
    public function usageLogs(): HasMany
    {
        return $this->hasMany(ReplyUsageLog::class, 'template_id');
    }

    /**
     * 使用回数をインクリメント
     */
    public function incrementUsage(): void
    {
        $this->increment('times_used');

        // 使用ログを記録
        $this->usageLogs()->create([
            'used_at' => now(),
        ]);
    }

    /**
     * 類似した返信テンプレートを検索
     *
     * @param int $userId
     * @param array $queryEmbedding
     * @param float $threshold
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function findSimilar(int $userId, array $queryEmbedding, float $threshold = 0.8)
    {
        $embeddingService = new \App\Services\GeminiEmbeddingService();

        // ユーザーのembeddingがあるテンプレートを取得
        $templates = self::where('user_id', $userId)
            ->whereNotNull('embedding')
            ->get();

        $similarTemplates = [];

        foreach ($templates as $template) {
            if (empty($template->embedding)) {
                continue;
            }

            $similarity = $embeddingService->cosineSimilarity($queryEmbedding, $template->embedding);

            if ($similarity >= $threshold) {
                $template->similarity_score = $similarity;
                $similarTemplates[] = $template;
            }
        }

        // 類似度の高い順にソート
        usort($similarTemplates, function ($a, $b) {
            return $b->similarity_score <=> $a->similarity_score;
        });

        return collect($similarTemplates);
    }
}
