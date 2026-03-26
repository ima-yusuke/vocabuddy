<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Plan;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free',
                'slug' => 'free',
                'word_limit' => 50,
                'ai_reply_daily_limit' => 2,
                'ai_reply_monthly_limit' => null,
                'price_monthly' => 0,
                'price_yearly' => 0,
                'ai_model' => 'flash',
                'is_active' => true,
            ],
            [
                'name' => 'Basic',
                'slug' => 'basic',
                'word_limit' => 300,
                'ai_reply_daily_limit' => 10,
                'ai_reply_monthly_limit' => null,
                'price_monthly' => 400,
                'price_yearly' => 4000,
                'ai_model' => 'flash',
                'is_active' => true,
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'word_limit' => null, // 無制限
                'ai_reply_daily_limit' => null,
                'ai_reply_monthly_limit' => 300,
                'price_monthly' => 1000,
                'price_yearly' => 10000,
                'ai_model' => 'pro',
                'is_active' => true,
            ],
            [
                'name' => 'Premium',
                'slug' => 'premium',
                'word_limit' => null, // 無制限
                'ai_reply_daily_limit' => null,
                'ai_reply_monthly_limit' => null, // 無制限
                'price_monthly' => 1750,
                'price_yearly' => 17500,
                'ai_model' => 'pro',
                'is_active' => true,
            ],
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'word_limit' => null, // 無制限
                'ai_reply_daily_limit' => null, // 無制限
                'ai_reply_monthly_limit' => null, // 無制限
                'price_monthly' => 0, // 無料
                'price_yearly' => 0, // 無料
                'ai_model' => 'pro',
                'is_active' => false, // 一般ユーザーには非表示
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
}
