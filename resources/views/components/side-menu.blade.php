<div id="hamburger_icon" class="fixed right-6 top-6 z-50 cursor-pointer bg-white/80 backdrop-blur-sm hover:bg-white p-3 rounded-xl shadow-soft hover:shadow-soft-lg transition-all duration-300 border border-primary-100">
    <svg class="w-6 h-6 text-primary-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
    </svg>
</div>
<div id="close_icon" class="hidden fixed right-6 top-6 z-50 cursor-pointer bg-white/90 backdrop-blur-sm hover:bg-white p-3 rounded-xl shadow-soft-lg transition-all duration-300 border border-primary-200">
    <svg class="w-6 h-6 text-primary-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
    </svg>
</div>
<div class="side_wrapper w-full">
    <div class="side_menu_off pt-8 px-6">
        <div class="mb-8">
            <h5 class="text-sm font-semibold text-white/60 uppercase tracking-wider mb-1">Navigation</h5>
            <div class="h-0.5 w-12 bg-gradient-to-r from-accent-400 to-transparent rounded-full"></div>
        </div>

        <div class="py-4 overflow-y-auto">
            <ul class="space-y-3">
                @guest
                    <li class="side_li">
                        <a href="{{ route('landing') }}" class="flex items-center px-4 py-3 text-white hover:bg-white/10 rounded-xl transition-all duration-300 group">
                            <svg class="w-5 h-5 mr-3 text-accent-400 group-hover:text-accent-300 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            <span class="text-lg font-semibold">ホーム</span>
                        </a>
                    </li>
                    <li class="side_li">
                        <a href="{{ route('login') }}" class="flex items-center px-4 py-3 text-white hover:bg-white/10 rounded-xl transition-all duration-300 group">
                            <svg class="w-5 h-5 mr-3 text-accent-400 group-hover:text-accent-300 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                            </svg>
                            <span class="text-lg font-semibold">ログイン</span>
                        </a>
                    </li>
                    <li class="side_li">
                        <a href="{{ route('register') }}" class="flex items-center px-4 py-3 text-white hover:bg-white/10 rounded-xl transition-all duration-300 group">
                            <svg class="w-5 h-5 mr-3 text-accent-400 group-hover:text-accent-300 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                            <span class="text-lg font-semibold">新規登録</span>
                        </a>
                    </li>
                @else
                    <li class="side_li">
                        <a href="{{ route('words.index') }}" class="flex items-center px-4 py-3 text-white hover:bg-white/10 rounded-xl transition-all duration-300 group">
                            <svg class="w-5 h-5 mr-3 text-accent-400 group-hover:text-accent-300 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                            <span class="text-lg font-semibold">単語帳</span>
                        </a>
                    </li>
                    <li class="side_li">
                        <a href="{{ route('ShowTest') }}" class="flex items-center px-4 py-3 text-white hover:bg-white/10 rounded-xl transition-all duration-300 group">
                            <svg class="w-5 h-5 mr-3 text-accent-400 group-hover:text-accent-300 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                            </svg>
                            <span class="text-lg font-semibold">単語テスト</span>
                        </a>
                    </li>
                    <li class="side_li">
                        <a href="{{ route('ShowReplyAssistant') }}" class="flex items-center px-4 py-3 text-white hover:bg-white/10 rounded-xl transition-all duration-300 group">
                            <svg class="w-5 h-5 mr-3 text-accent-400 group-hover:text-accent-300 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                            </svg>
                            <span class="text-lg font-semibold">返信アシスタント</span>
                        </a>
                    </li>
                    <li class="side_li">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center px-4 py-3 text-white hover:bg-white/10 rounded-xl transition-all duration-300 group">
                                <svg class="w-5 h-5 mr-3 text-accent-400 group-hover:text-accent-300 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                                <span class="text-lg font-semibold">ログアウト</span>
                            </button>
                        </form>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</div>
