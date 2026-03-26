<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable,HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * ユーザーの単語
     */
    public function words()
    {
        return $this->hasMany(Word::class);
    }

    /**
     * 返信テンプレートとのリレーション
     */
    public function replyTemplates()
    {
        return $this->hasMany(ReplyTemplate::class);
    }

    /**
     * ユーザーのサブスクリプション
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * 現在のアクティブなサブスクリプション
     */
    public function subscription()
    {
        return $this->hasOne(Subscription::class)->active()->latest();
    }

    /**
     * AI使用ログ
     */
    public function aiUsageLogs()
    {
        return $this->hasMany(AiUsageLog::class);
    }

    /**
     * 現在のプランを取得（サブスクリプションがない場合はFreeプラン）
     */
    public function currentPlan()
    {
        $subscription = $this->subscription;

        if ($subscription && $subscription->isActive()) {
            return $subscription->plan;
        }

        // デフォルトでFreeプラン
        return Plan::where('slug', 'free')->first();
    }

    /**
     * 苦手単語
     */
    public function weakWords()
    {
        return $this->hasMany(WeakWord::class);
    }

    /**
     * プランに応じたAIモデル名を取得
     *
     * @param string $defaultModel コントローラーで使用中のデフォルトモデル
     * @return string
     */
    public function getAiModelName(string $defaultModel = 'gemini-2.5-flash'): string
    {
        $plan = $this->currentPlan();

        // Adminプランは現在のモデルをそのまま使用
        if ($plan->slug === 'admin') {
            return $defaultModel;
        }

        // プランのai_modelフィールドに基づいてモデル名を決定
        return match($plan->ai_model) {
            'pro' => 'gemini-2.0-flash-exp', // Pro/Premium用の高性能モデル
            'flash' => 'gemini-2.5-flash', // Free/Basic用の軽量モデル
            default => 'gemini-2.5-flash', // デフォルトは軽量モデル
        };
    }
}
