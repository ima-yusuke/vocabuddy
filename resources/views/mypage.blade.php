<x-template title="マイページ">
    <div class="min-h-screen bg-[#ffeb54]">
        <section class="py-16 px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto">
                <div class="mb-10">
                    <h1 class="text-3xl font-bold text-black">マイページ</h1>
                    <p class="mt-2 text-black">アカウント情報の変更ができます</p>
                </div>

                <div class="space-y-6">
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
