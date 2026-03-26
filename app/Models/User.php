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
}
