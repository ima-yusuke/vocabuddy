<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'word_limit',
        'ai_reply_daily_limit',
        'ai_reply_monthly_limit',
        'ai_autocomplete_daily_limit',
        'ai_autocomplete_monthly_limit',
        'price_monthly',
        'price_yearly',
        'ai_model',
        'is_active',
    ];

    protected $casts = [
        'word_limit' => 'integer',
        'ai_reply_daily_limit' => 'integer',
        'ai_reply_monthly_limit' => 'integer',
        'ai_autocomplete_daily_limit' => 'integer',
        'ai_autocomplete_monthly_limit' => 'integer',
        'price_monthly' => 'integer',
        'price_yearly' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * アクティブなプランのみ取得
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * このプランを使用しているサブスクリプション
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
