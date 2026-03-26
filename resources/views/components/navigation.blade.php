<nav class="fixed w-full bg-white border-b border-black z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center">
                <a href="/" class="text-2xl font-bold text-black">VocaBuddy</a>
            </div>
            <div class="hidden md:flex items-center space-x-8">
                @auth
                    {{-- ログイン済み：アプリ内メニュー --}}
                    <a href="{{ route('words.index') }}" class="text-sm font-medium {{ request()->routeIs('words.*') ? 'text-black' : 'text-gray-700 hover:text-black' }} transition">
                        単語帳
                    </a>
                    <a href="{{ route('ShowTest') }}" class="text-sm font-medium {{ request()->routeIs('ShowTest') || request()->routeIs('StartTest') || request()->routeIs('ShowQuestion') ? 'text-black' : 'text-gray-700 hover:text-black' }} transition">
                        単語テスト
                    </a>
                    <a href="{{ route('ShowReplyAssistant') }}" class="text-sm font-medium {{ request()->routeIs('ShowReplyAssistant') ? 'text-black' : 'text-gray-700 hover:text-black' }} transition">
                        AI返信
                    </a>
                    <a href="{{ route('ShowReplyHistory') }}" class="text-sm font-medium {{ request()->routeIs('ShowReplyHistory') ? 'text-black' : 'text-gray-700 hover:text-black' }} transition">
                        返信履歴
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-sm font-medium text-gray-700 hover:text-black transition">
                            ログアウト
                        </button>
                    </form>
                @else
                    {{-- 未ログイン：ランディングページメニュー --}}
                    <a href="#features" class="text-sm font-medium text-gray-700 hover:text-black transition">機能</a>
                    <a href="#pricing" class="text-sm font-medium text-gray-700 hover:text-black transition">料金</a>
                    <a href="{{ route('login') }}" class="text-sm font-medium text-gray-700 hover:text-black transition">ログイン</a>
                    <a href="{{ route('register') }}" class="bg-[#ffeb54] hover:bg-[#ffe135] text-black px-6 py-2 rounded-full text-sm font-medium transition">
                        無料で始める
                    </a>
                @endauth
            </div>
        </div>
    </div>
</nav>

<!-- スペーサー（固定ナビゲーションの高さ分） -->
<div class="h-16"></div>
