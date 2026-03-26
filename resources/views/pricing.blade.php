<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>料金プラン - VocaBuddy</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-gray-900">
    <!-- ナビゲーション -->
    <nav class="border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="/" class="text-2xl font-bold text-gray-900">VocaBuddy</a>
                </div>
                <div class="hidden md:flex items-center space-x-8">
                    <a href="/#features" class="text-gray-600 hover:text-gray-900 transition">機能</a>
                    <a href="/pricing" class="text-gray-900 font-semibold">価格</a>
                    @guest
                        <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900 transition">ログイン</a>
                        <a href="{{ route('register') }}" class="bg-gray-900 text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition">新規登録</a>
                    @else
                        <a href="{{ route('words.index') }}" class="text-gray-600 hover:text-gray-900 transition">単語帳</a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-600 hover:text-gray-900 transition">ログアウト</button>
                        </form>
                    @endguest
                </div>
            </div>
        </div>
    </nav>

    <!-- ヘッダー -->
    <section class="py-16 text-center">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">料金プラン</h1>
            <p class="text-xl text-gray-600">あなたに最適なプランを選んでください</p>
        </div>
    </section>

    <!-- プラン比較表 -->
    <section class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($plans as $plan)
                <div class="bg-white p-8 rounded-2xl border-2 {{ $plan['type'] === 'standard' ? 'border-gray-900 shadow-lg' : 'border-gray-200' }}">
                    @if($plan['type'] === 'standard')
                    <div class="text-xs font-semibold text-gray-900 mb-2">おすすめ</div>
                    @endif
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ $plan['name'] }}</h3>
                    <div class="mb-4">
                        <span class="text-4xl font-bold text-gray-900">¥{{ number_format($plan['monthly']) }}</span>
                        <span class="text-gray-600">/月</span>
                    </div>
                    @if($plan['yearly'] > 0)
                    <div class="text-sm text-gray-600 mb-6">
                        年額 ¥{{ number_format($plan['yearly']) }}
                        <span class="text-xs text-green-600">({{ round((1 - $plan['yearly'] / ($plan['monthly'] * 12)) * 100) }}%お得)</span>
                    </div>
                    @endif
                    <div class="mb-6">
                        <div class="text-lg font-semibold text-gray-900 mb-2">
                            @if($plan['limit'])
                                {{ $plan['limit'] }}単語まで
                            @else
                                無制限
                            @endif
                        </div>
                    </div>
                    <ul class="space-y-3 mb-8">
                        @foreach($plan['features'] as $feature)
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-gray-900 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-600">{{ $feature }}</span>
                        </li>
                        @endforeach
                    </ul>
                    @if($plan['type'] === 'free')
                    <a href="{{ route('register') }}" class="block w-full text-center bg-gray-900 text-white px-6 py-3 rounded-lg font-semibold hover:bg-gray-800 transition">
                        無料で始める
                    </a>
                    @else
                    <button class="block w-full text-center bg-gray-200 text-gray-500 px-6 py-3 rounded-lg font-semibold cursor-not-allowed">
                        準備中
                    </button>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-center text-gray-900 mb-12">よくある質問</h2>
            <div class="space-y-6">
                <div class="bg-white p-6 rounded-lg border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">無料プランでも全機能使えますか？</h3>
                    <p class="text-gray-600">はい、20単語までの登録制限がありますが、AI自動補完、単語テスト、返信アシスタントなど全機能をご利用いただけます。</p>
                </div>
                <div class="bg-white p-6 rounded-lg border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">プランはいつでも変更できますか？</h3>
                    <p class="text-gray-600">はい、いつでもアップグレード・ダウングレードが可能です。（フェーズ3で実装予定）</p>
                </div>
                <div class="bg-white p-6 rounded-lg border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">年額プランの方がお得ですか？</h3>
                    <p class="text-gray-600">はい、年額プランは月額プランと比べて約17%お得になります。</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">
                今すぐ始めよう
            </h2>
            <p class="text-xl text-gray-600 mb-8">
                20単語まで無料。クレジットカード不要。
            </p>
            <a href="{{ route('register') }}" class="inline-block bg-gray-900 text-white px-10 py-4 rounded-lg text-lg font-semibold hover:bg-gray-800 transition transform hover:scale-105">
                無料で始める
            </a>
        </div>
    </section>

    <!-- フッター -->
    <footer class="bg-gray-50 border-t border-gray-200 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center text-gray-600">
                <div class="mb-4">
                    <a href="#" class="hover:text-gray-900 transition mx-3">プライバシーポリシー</a>
                    <a href="#" class="hover:text-gray-900 transition mx-3">利用規約</a>
                    <a href="#" class="hover:text-gray-900 transition mx-3">お問い合わせ</a>
                </div>
                <p>&copy; 2026 VocaBuddy. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
