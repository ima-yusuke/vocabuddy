<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ログイン - VocaBuddy</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Zen+Kaku+Gothic+New:wght@400;700;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Zen Kaku Gothic New', sans-serif;
            font-weight: 400;
        }
    </style>
</head>
<body class="antialiased bg-[#ffeb54] min-h-screen flex items-center justify-center px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <!-- ロゴ -->
        <div class="text-center mb-8">
            <a href="/" class="inline-block">
                <h1 class="text-4xl font-bold text-black">VocaBuddy</h1>
            </a>
            <p class="mt-2 text-black">アカウントにログイン</p>
        </div>

        <!-- ログインフォーム -->
        <div class="bg-white rounded-2xl shadow-lg p-8 border-2 border-black">
            <!-- セッションステータス -->
            @if (session('status'))
                <div class="mb-4 bg-green-50 border-2 border-green-500 text-green-700 px-4 py-3 rounded-xl">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- メールアドレス -->
                <div class="mb-6">
                    <label for="email" class="block text-sm font-semibold text-black mb-2">
                        メールアドレス
                    </label>
                    <input id="email"
                           type="email"
                           name="email"
                           value="{{ old('email') }}"
                           required
                           autofocus
                           autocomplete="username"
                           class="w-full border-2 border-black rounded-xl px-4 py-3 focus:outline-none focus:border-[#ffeb54] focus:ring-4 focus:ring-[#ffeb54]/20 transition-all duration-200 bg-white text-black placeholder-gray-400">
                    @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- パスワード -->
                <div class="mb-6">
                    <label for="password" class="block text-sm font-semibold text-black mb-2">
                        パスワード
                    </label>
                    <input id="password"
                           type="password"
                           name="password"
                           required
                           autocomplete="current-password"
                           class="w-full border-2 border-black rounded-xl px-4 py-3 focus:outline-none focus:border-[#ffeb54] focus:ring-4 focus:ring-[#ffeb54]/20 transition-all duration-200 bg-white text-black placeholder-gray-400">
                    @error('password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- ログイン状態を保持 -->
                <div class="mb-6">
                    <label for="remember_me" class="inline-flex items-center">
                        <input id="remember_me"
                               type="checkbox"
                               name="remember"
                               class="rounded border-2 border-black text-[#ffeb54] focus:ring-[#ffeb54] focus:ring-offset-0">
                        <span class="ml-2 text-sm text-black font-medium">ログイン状態を保持する</span>
                    </label>
                </div>

                <!-- 送信ボタン -->
                <div class="mb-4">
                    <button type="submit"
                            class="w-full bg-[#ffeb54] hover:bg-[#ffeb54]/80 text-black font-bold py-3 px-4 rounded-xl border-2 border-black transition-all duration-300 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[2px] hover:translate-y-[2px]">
                        ログイン
                    </button>
                </div>

                <!-- リンク -->
                <div class="flex items-center justify-between text-sm">
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-black hover:underline font-medium">
                            パスワードをお忘れですか？
                        </a>
                    @endif
                    <a href="{{ route('register') }}" class="text-black hover:underline font-medium">
                        新規登録
                    </a>
                </div>
            </form>
        </div>

        <!-- ホームに戻る -->
        <div class="text-center mt-6">
            <a href="/" class="text-sm text-black hover:underline font-medium">
                ← ホームに戻る
            </a>
        </div>
    </div>
</body>
</html>
