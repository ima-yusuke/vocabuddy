<x-template title="マイページ">
    <div class="min-h-screen bg-[#ffeb54]">
        <section class="py-16 px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto">
                <div class="mb-10">
                    <h1 class="text-3xl font-bold text-black">マイページ</h1>
                    <p class="mt-2 text-black">アカウント情報の変更ができます</p>
                </div>

                <div class="space-y-6">
                    <!-- プラン情報 -->
                    <div class="bg-white border-2 border-black rounded-2xl p-8 shadow-soft-lg">
                        <h2 class="text-xl font-bold text-black mb-2">現在のプラン</h2>
                        <p class="text-sm text-gray-600 mb-6">ご利用中のプラン情報</p>

                        @php
                            $plan = $user->currentPlan();
                            $subscription = $user->subscription;
                        @endphp

                        <div class="space-y-4">
                            <!-- プラン名 -->
                            @if($plan->slug === 'admin')
                                <div class="p-4 bg-gradient-to-r from-purple-600 to-indigo-600 rounded-xl border-2 border-black">
                                    <div class="flex items-center">
                                        <svg class="w-8 h-8 text-white mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                        </svg>
                                        <div>
                                            <p class="text-sm text-white/80 mb-1">権限</p>
                                            <p class="text-2xl font-bold text-white">管理者</p>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="flex items-center justify-between p-4 bg-[#ffeb54]/10 rounded-xl border border-[#ffeb54]">
                                    <div>
                                        <p class="text-sm text-gray-600 mb-1">プラン</p>
                                        <p class="text-2xl font-bold text-black">{{ $plan->name }}</p>
                                    </div>
                                    @if($plan->slug !== 'free')
                                        <div class="text-right">
                                            <p class="text-sm text-gray-600 mb-1">月額料金</p>
                                            <p class="text-xl font-bold text-black">¥{{ number_format($plan->price_monthly) }}</p>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <!-- 加入日 -->
                            @if($subscription && $subscription->isActive())
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="p-4 bg-gray-50 rounded-xl">
                                        <p class="text-sm text-gray-600 mb-1">加入日</p>
                                        <p class="text-lg font-semibold text-black">{{ $subscription->started_at->format('Y年m月d日') }}</p>
                                    </div>
                                    <div class="p-4 bg-gray-50 rounded-xl">
                                        <p class="text-sm text-gray-600 mb-1">ステータス</p>
                                        <p class="text-lg font-semibold text-green-600">
                                            @if($subscription->status === 'active')
                                                ✓ 有効
                                            @else
                                                {{ $subscription->status }}
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                @if($subscription->ends_at)
                                    <div class="p-4 bg-orange-50 border border-orange-200 rounded-xl">
                                        <p class="text-sm text-orange-800">
                                            <span class="font-semibold">更新日:</span> {{ $subscription->ends_at->format('Y年m月d日') }}
                                        </p>
                                    </div>
                                @endif
                            @else
                                <div class="p-4 bg-gray-50 rounded-xl">
                                    <p class="text-sm text-gray-600 mb-1">ステータス</p>
                                    <p class="text-lg font-semibold text-black">Freeプラン</p>
                                </div>
                            @endif

                            <!-- プラン詳細 -->
                            <div class="border-t-2 border-gray-200 pt-4">
                                <p class="text-sm font-semibold text-black mb-3">プラン内容</p>
                                <div class="space-y-2 text-sm text-gray-700">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-[#ffeb54] mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span>単語登録: {{ $plan->word_limit ? number_format($plan->word_limit) . '単語まで' : '無制限' }}</span>
                                    </div>
                                    @if($plan->ai_reply_daily_limit)
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 text-[#ffeb54] mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            <span>AI返信: {{ number_format($plan->ai_reply_daily_limit) }}回/日</span>
                                        </div>
                                    @endif
                                    @if($plan->ai_reply_monthly_limit)
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 text-[#ffeb54] mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            <span>AI返信: {{ number_format($plan->ai_reply_monthly_limit) }}回/月</span>
                                        </div>
                                    @endif
                                    @if($plan->ai_autocomplete_daily_limit)
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 text-[#ffeb54] mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            <span>AI単語補完: {{ number_format($plan->ai_autocomplete_daily_limit) }}回/日</span>
                                        </div>
                                    @endif
                                    @if($plan->ai_autocomplete_monthly_limit)
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 text-[#ffeb54] mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            <span>AI単語補完: {{ number_format($plan->ai_autocomplete_monthly_limit) }}回/月</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- プラン変更ボタン（管理者以外） -->
                            @if($plan->slug !== 'admin')
                                <div class="pt-4">
                                    <a href="{{ route('pricing') }}"
                                        class="inline-block bg-[#ffeb54] hover:bg-[#ffe135] text-black px-6 py-3 rounded-xl font-semibold transition-all duration-300 transform hover:-translate-y-0.5 shadow-soft hover:shadow-soft-lg">
                                        プランを変更する
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- メールアドレス変更 -->
                    <div class="bg-white border-2 border-black rounded-2xl p-8 shadow-soft-lg">
                        <h2 class="text-xl font-bold text-black mb-2">メールアドレスとユーザー名</h2>
                        <p class="text-sm text-gray-600 mb-6">アカウントのメールアドレスとユーザー名を更新できます</p>

                        <form id="send-verification" method="post" action="{{ route('verification.send') }}">
                            @csrf
                        </form>

                        <form method="post" action="{{ route('mypage.update') }}" class="space-y-6">
                            @csrf
                            @method('patch')

                            <div>
                                <label for="name" class="block text-sm font-semibold text-black mb-2">
                                    ユーザー名
                                </label>
                                <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autofocus
                                    class="w-full border-2 border-black rounded-xl px-5 py-3 focus:outline-none focus:border-[#ffeb54] focus:ring-4 focus:ring-[#ffeb54]/20 transition-all duration-200 bg-white text-black">
                                @error('name')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-semibold text-black mb-2">
                                    メールアドレス
                                </label>
                                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required
                                    class="w-full border-2 border-black rounded-xl px-5 py-3 focus:outline-none focus:border-[#ffeb54] focus:ring-4 focus:ring-[#ffeb54]/20 transition-all duration-200 bg-white text-black">
                                @error('email')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror

                                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                                    <div class="mt-3 p-4 bg-yellow-50 border border-yellow-200 rounded-xl">
                                        <p class="text-sm text-gray-800">
                                            メールアドレスが未確認です。
                                            <button form="send-verification" class="underline text-black hover:text-gray-700 font-semibold">
                                                確認メールを再送信
                                            </button>
                                        </p>

                                        @if (session('status') === 'verification-link-sent')
                                            <p class="mt-2 text-sm text-green-600 font-semibold">
                                                新しい確認リンクが送信されました。
                                            </p>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <div class="flex items-center gap-4">
                                <button type="submit"
                                    class="bg-black hover:bg-[#1A1A1A] text-white px-6 py-3 rounded-xl font-semibold shadow-soft hover:shadow-soft-lg transition-all duration-300 transform hover:-translate-y-0.5">
                                    保存する
                                </button>

                                @if (session('status') === 'profile-updated')
                                    <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                                        class="text-sm text-green-600 font-semibold">
                                        保存されました
                                    </p>
                                @endif
                            </div>
                        </form>
                    </div>

                    <!-- パスワード変更 -->
                    <div class="bg-white border-2 border-black rounded-2xl p-8 shadow-soft-lg">
                        <h2 class="text-xl font-bold text-black mb-2">パスワード変更</h2>
                        <p class="text-sm text-gray-600 mb-6">長くてランダムなパスワードを使用してアカウントを安全に保ちましょう</p>

                        <form method="post" action="{{ route('password.update') }}" class="space-y-6">
                            @csrf
                            @method('put')

                            <div>
                                <label for="update_password_current_password" class="block text-sm font-semibold text-black mb-2">
                                    現在のパスワード
                                </label>
                                <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password"
                                    class="w-full border-2 border-black rounded-xl px-5 py-3 focus:outline-none focus:border-[#ffeb54] focus:ring-4 focus:ring-[#ffeb54]/20 transition-all duration-200 bg-white text-black">
                                @if($errors->updatePassword->has('current_password'))
                                    <p class="mt-2 text-sm text-red-600">{{ $errors->updatePassword->first('current_password') }}</p>
                                @endif
                            </div>

                            <div>
                                <label for="update_password_password" class="block text-sm font-semibold text-black mb-2">
                                    新しいパスワード
                                </label>
                                <input id="update_password_password" name="password" type="password" autocomplete="new-password"
                                    class="w-full border-2 border-black rounded-xl px-5 py-3 focus:outline-none focus:border-[#ffeb54] focus:ring-4 focus:ring-[#ffeb54]/20 transition-all duration-200 bg-white text-black">
                                @if($errors->updatePassword->has('password'))
                                    <p class="mt-2 text-sm text-red-600">{{ $errors->updatePassword->first('password') }}</p>
                                @endif
                            </div>

                            <div>
                                <label for="update_password_password_confirmation" class="block text-sm font-semibold text-black mb-2">
                                    新しいパスワード（確認）
                                </label>
                                <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                                    class="w-full border-2 border-black rounded-xl px-5 py-3 focus:outline-none focus:border-[#ffeb54] focus:ring-4 focus:ring-[#ffeb54]/20 transition-all duration-200 bg-white text-black">
                                @if($errors->updatePassword->has('password_confirmation'))
                                    <p class="mt-2 text-sm text-red-600">{{ $errors->updatePassword->first('password_confirmation') }}</p>
                                @endif
                            </div>

                            <div class="flex items-center gap-4">
                                <button type="submit"
                                    class="bg-black hover:bg-[#1A1A1A] text-white px-6 py-3 rounded-xl font-semibold shadow-soft hover:shadow-soft-lg transition-all duration-300 transform hover:-translate-y-0.5">
                                    パスワードを変更
                                </button>

                                @if (session('status') === 'password-updated')
                                    <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                                        class="text-sm text-green-600 font-semibold">
                                        変更されました
                                    </p>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-template>
