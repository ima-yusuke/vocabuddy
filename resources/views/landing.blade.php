<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>VocaBuddy - AI搭載の英語学習アプリ</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Zen+Kaku+Gothic+New:wght@400;700;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/css/side-menu.css', 'resources/js/side-menu.js', 'resources/js/app.js'])
    <style>
        :root {
            --color-primary: #ffeb54;
            --color-black: #1A1A1A;
            --color-white: #FFFFFF;
        }

        body {
            font-family: 'Zen Kaku Gothic New', sans-serif;
            font-weight: 400;
            background-color: var(--color-white);
            color: var(--color-black);
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Zen Kaku Gothic New', sans-serif;
            font-weight: 700;
        }

        .bg-yellow {
            background-color: var(--color-primary);
        }

        .bg-black {
            background-color: var(--color-black);
        }

        .bg-white {
            background-color: var(--color-white);
        }

        .text-yellow {
            color: var(--color-primary);
        }

        .animate-fade-in {
            animation: fadeIn 0.8s ease-out forwards;
            opacity: 0;
        }

        .delay-100 { animation-delay: 0.1s; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-300 { animation-delay: 0.3s; }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .feature-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background-color: var(--color-primary);
            color: var(--color-black);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 235, 84, 0.4);
        }

        .btn-white {
            background-color: var(--color-white);
            color: var(--color-black);
            transition: transform 0.2s ease;
        }

        .btn-white:hover {
            transform: translateY(-2px);
        }

        .section-divider {
            height: 1px;
            background-color: #E5E5E5;
            margin: 4rem 0;
        }
    </style>
</head>
<body class="antialiased">
    <x-side-menu></x-side-menu>
    <x-navigation></x-navigation>

    <!-- ヒーローセクション -->
    <section class="pt-32 pb-20 md:pt-40 md:pb-32 bg-yellow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto text-center">
                <div class="inline-block mb-6 px-4 py-2 bg-black text-white rounded-full animate-fade-in">
                    <span class="text-sm font-medium">✨ 20単語まで完全無料</span>
                </div>
                <h1 class="text-5xl md:text-7xl font-bold mb-6 leading-tight animate-fade-in delay-100">
                    映画や日常で<br>
                    出会った英単語を、<br>
                    自分だけの単語帳に
                </h1>
                <p class="text-xl md:text-2xl mb-10 leading-relaxed animate-fade-in delay-200">
                    AIが自動補完。返信文も生成。<br>
                    クレジットカード不要で今すぐ始められる
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center animate-fade-in delay-300">
                    <a href="{{ route('register') }}" class="btn-primary px-10 py-4 rounded-full text-lg font-medium">
                        無料で始める
                    </a>
                    <a href="#features" class="bg-black text-white px-10 py-4 rounded-full text-lg font-medium hover:bg-gray-800 transition">
                        詳しく見る
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- 問題提起セクション -->
    <section class="py-20 bg-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold mb-6">
                    英語学習、<br class="md:hidden">こんな悩みありませんか？
                </h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-20">
                <div class="text-center p-8">
                    <div class="w-20 h-20 mx-auto mb-6 rounded-2xl bg-gray-100 flex items-center justify-center text-4xl">
                        😰
                    </div>
                    <h3 class="text-xl font-bold mb-3">単語帳が続かない</h3>
                    <p class="text-gray-600 leading-relaxed">市販の単語帳は自分に関係ない単語ばかりで飽きてしまう</p>
                </div>
                <div class="text-center p-8">
                    <div class="w-20 h-20 mx-auto mb-6 rounded-2xl bg-gray-100 flex items-center justify-center text-4xl">
                        ⏰
                    </div>
                    <h3 class="text-xl font-bold mb-3">調べるのに時間がかかる</h3>
                    <p class="text-gray-600 leading-relaxed">辞書で意味を調べて、ノートに書いて...手間がかかりすぎる</p>
                </div>
                <div class="text-center p-8">
                    <div class="w-20 h-20 mx-auto mb-6 rounded-2xl bg-gray-100 flex items-center justify-center text-4xl">
                        📝
                    </div>
                    <h3 class="text-xl font-bold mb-3">覚えても使えない</h3>
                    <p class="text-gray-600 leading-relaxed">単語を覚えても、実際の会話で使う機会がない</p>
                </div>
            </div>

            <!-- 解決策 -->
            <div class="bg-black rounded-3xl p-12 md:p-16 text-center">
                <div class="max-w-4xl mx-auto text-white">
                    <div class="text-6xl mb-8">✨</div>
                    <h3 class="text-3xl md:text-4xl font-bold mb-8">VocaBuddyなら、すべて解決</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-left">
                        <div class="bg-yellow text-black rounded-2xl p-6">
                            <div class="text-2xl font-bold mb-3">📚</div>
                            <div class="text-lg font-bold mb-2">自分専用の単語帳</div>
                            <p class="text-sm">映画や日常で出会った単語だけを登録。興味のある単語だから続く</p>
                        </div>
                        <div class="bg-yellow text-black rounded-2xl p-6">
                            <div class="text-2xl font-bold mb-3">⚡</div>
                            <div class="text-lg font-bold mb-2">AI自動補完</div>
                            <p class="text-sm">単語を入力するだけで発音・意味を自動取得。10秒で登録完了</p>
                        </div>
                        <div class="bg-yellow text-black rounded-2xl p-6">
                            <div class="text-2xl font-bold mb-3">💬</div>
                            <div class="text-lg font-bold mb-2">実践的に使える</div>
                            <p class="text-sm">返信アシスタントで登録した単語を使った英文を自動生成</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider max-w-6xl mx-auto"></div>

    <!-- 主要機能セクション -->
    <section id="features" class="py-20 bg-yellow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold mb-4">
                    4つの主要機能
                </h2>
                <p class="text-xl">すべて無料プランから使える</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- 機能1 -->
                <div class="feature-card bg-white p-10 rounded-3xl border-2 border-black">
                    <div class="w-16 h-16 rounded-2xl bg-yellow flex items-center justify-center text-3xl mb-6">
                        📚
                    </div>
                    <h3 class="text-2xl font-bold mb-4">自分だけの単語帳</h3>
                    <p class="text-gray-700 leading-relaxed text-lg">映画や日常で学んだ英単語を登録して、自分専用の単語帳を作成。市販の単語帳とは違い、あなたが本当に使いたい単語だけを集められます。</p>
                </div>
                <!-- 機能2 -->
                <div class="feature-card bg-white p-10 rounded-3xl border-2 border-black">
                    <div class="w-16 h-16 rounded-2xl bg-yellow flex items-center justify-center text-3xl mb-6">
                        🤖
                    </div>
                    <h3 class="text-2xl font-bold mb-4">AI自動補完</h3>
                    <p class="text-gray-700 leading-relaxed text-lg">英単語を入力するだけで、AIが発音・品詞・意味を自動取得。辞書を引く時間が90%削減され、10秒で登録完了します。</p>
                </div>
                <!-- 機能3 -->
                <div class="feature-card bg-white p-10 rounded-3xl border-2 border-black">
                    <div class="w-16 h-16 rounded-2xl bg-yellow flex items-center justify-center text-3xl mb-6">
                        ✏️
                    </div>
                    <h3 class="text-2xl font-bold mb-4">単語テスト</h3>
                    <p class="text-gray-700 leading-relaxed text-lg">登録した単語から4択クイズを自動生成。ゲーム感覚で楽しく学習でき、自分の理解度を確認できます。</p>
                </div>
                <!-- 機能4 -->
                <div class="feature-card bg-white p-10 rounded-3xl border-2 border-black">
                    <div class="w-16 h-16 rounded-2xl bg-yellow flex items-center justify-center text-3xl mb-6">
                        💬
                    </div>
                    <h3 class="text-2xl font-bold mb-4">AI返信アシスタント</h3>
                    <p class="text-gray-700 leading-relaxed text-lg">登録した単語を使った自然な英語の返信文をAIが生成。覚えた単語を実際の会話で使える形で練習できます。</p>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider max-w-6xl mx-auto"></div>

    <!-- 統計データセクション -->
    <section class="py-20 bg-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold mb-4">
                    数字で見るVocaBuddy
                </h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center p-10 bg-white rounded-3xl border-2 border-black">
                    <div class="text-6xl font-bold mb-2 text-black">90%</div>
                    <div class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-3">時間短縮</div>
                    <p class="text-gray-600">AIが自動で意味を取得。辞書を引く時間が大幅に削減</p>
                </div>
                <div class="text-center p-10 bg-white rounded-3xl border-2 border-black">
                    <div class="text-6xl font-bold mb-2 text-black">10秒</div>
                    <div class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-3">で登録完了</div>
                    <p class="text-gray-600">単語を入力してボタンを押すだけ。すぐに単語帳に追加</p>
                </div>
                <div class="text-center p-10 bg-white rounded-3xl border-2 border-black">
                    <div class="text-6xl font-bold mb-2 text-black">無制限</div>
                    <div class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-3">プランあり</div>
                    <p class="text-gray-600">プレミアムプランなら単語登録数の制限なし</p>
                </div>
            </div>
        </div>
    </section>

    <!-- プラン・価格表セクション -->
    <section id="pricing" class="py-20 bg-yellow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold mb-4">
                    プラン・料金
                </h2>
                <p class="text-xl">まずは無料で始めて、必要に応じてアップグレード</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($plans as $plan)
                <div class="bg-white p-8 rounded-3xl border-2 border-black transition-all duration-300 {{ $plan['type'] === 'basic' ? 'scale-105 shadow-2xl' : '' }}">
                    @if($plan['type'] === 'basic')
                    <div class="inline-block px-3 py-1 bg-yellow rounded-full text-xs font-bold text-black mb-4">
                        おすすめ
                    </div>
                    @endif
                    <h3 class="text-2xl font-bold mb-2">{{ $plan['name'] }}</h3>
                    <div class="mb-6">
                        <span class="text-5xl font-bold">¥{{ number_format($plan['monthly']) }}</span>
                        <span class="text-gray-600">/月</span>
                    </div>
                    @if($plan['yearly'] > 0)
                    <div class="text-sm text-gray-600 mb-6">
                        年額 ¥{{ number_format($plan['yearly']) }}
                    </div>
                    @endif
                    <div class="mb-6 pb-6 border-b border-gray-200">
                        <div class="text-lg font-bold mb-2">
                            @if($plan['limit'])
                                {{ $plan['limit'] }}単語まで
                            @else
                                無制限
                            @endif
                        </div>
                    </div>
                    <ul class="space-y-4 mb-8">
                        @foreach($plan['features'] as $feature)
                        <li class="flex items-start">
                            <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0 text-black" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-600">{{ $feature }}</span>
                        </li>
                        @endforeach
                    </ul>
                    @if($plan['type'] === 'free')
                    <a href="{{ route('register') }}" class="btn-primary block w-full text-center px-6 py-3 rounded-full font-medium">
                        無料で始める
                    </a>
                    @else
                    <button class="block w-full text-center bg-gray-100 text-gray-400 px-6 py-3 rounded-full font-medium cursor-not-allowed">
                        準備中
                    </button>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <div class="section-divider max-w-6xl mx-auto"></div>

    <!-- 使い方セクション -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold mb-4">
                    3ステップで始められる
                </h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12 max-w-5xl mx-auto">
                <div class="text-center">
                    <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-yellow flex items-center justify-center text-2xl font-bold text-black">1</div>
                    <h3 class="text-xl font-bold mb-3">無料で新規登録</h3>
                    <p class="text-gray-600 leading-relaxed">メールアドレスとパスワードだけで、30秒で登録完了</p>
                </div>
                <div class="text-center">
                    <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-yellow flex items-center justify-center text-2xl font-bold text-black">2</div>
                    <h3 class="text-xl font-bold mb-3">単語を登録</h3>
                    <p class="text-gray-600 leading-relaxed">英単語を入力すると、AIが自動で発音・意味を補完</p>
                </div>
                <div class="text-center">
                    <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-yellow flex items-center justify-center text-2xl font-bold text-black">3</div>
                    <h3 class="text-xl font-bold mb-3">テスト・返信で活用</h3>
                    <p class="text-gray-600 leading-relaxed">単語テストで定着を確認、返信アシスタントで実践</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ユーザーの声セクション -->
    <section class="py-20 bg-yellow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold mb-4">
                    ユーザーの声
                </h2>
                <p class="text-xl">VocaBuddyを使っている方々の感想</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white p-8 rounded-3xl border-2 border-black">
                    <div class="flex mb-4">
                        @for($i = 0; $i < 5; $i++)
                        <svg class="w-5 h-5 text-black" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        @endfor
                    </div>
                    <p class="text-gray-700 mb-6 leading-relaxed">映画で出てきた単語をすぐ登録できるのが便利！AI補完で意味も自動で入るから、本当に楽になりました。</p>
                    <div class="text-sm font-medium text-gray-900">20代 大学生</div>
                </div>
                <div class="bg-white p-8 rounded-3xl border-2 border-black">
                    <div class="flex mb-4">
                        @for($i = 0; $i < 5; $i++)
                        <svg class="w-5 h-5 text-black" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        @endfor
                    </div>
                    <p class="text-gray-700 mb-6 leading-relaxed">返信アシスタント機能が最高！登録した単語を使った英文を作ってくれるから、実際の会話で使えるようになりました。</p>
                    <div class="text-sm font-medium text-gray-900">30代 会社員</div>
                </div>
                <div class="bg-white p-8 rounded-3xl border-2 border-black">
                    <div class="flex mb-4">
                        @for($i = 0; $i < 5; $i++)
                        <svg class="w-5 h-5 text-black" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        @endfor
                    </div>
                    <p class="text-gray-700 mb-6 leading-relaxed">無料プランでも十分使える！20単語あれば日常会話でよく使う単語は十分カバーできます。</p>
                    <div class="text-sm font-medium text-gray-900">40代 主婦</div>
                </div>
            </div>
        </div>
    </section>

    <!-- よくある質問セクション -->
    <section class="py-20 bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold mb-4">
                    よくある質問
                </h2>
            </div>
            <div class="space-y-6">
                <div class="bg-white p-8 rounded-3xl border-2 border-black">
                    <h3 class="text-xl font-bold mb-3">無料プランでも全機能使えますか？</h3>
                    <p class="text-gray-600 leading-relaxed">はい、20単語までの登録制限がありますが、AI自動補完、単語テスト、返信アシスタントなど全機能をご利用いただけます。</p>
                </div>
                <div class="bg-white p-8 rounded-3xl border-2 border-black">
                    <h3 class="text-xl font-bold mb-3">クレジットカードの登録は必要ですか？</h3>
                    <p class="text-gray-600 leading-relaxed">無料プランのご利用にはクレジットカードの登録は不要です。メールアドレスとパスワードだけで今すぐ始められます。</p>
                </div>
                <div class="bg-white p-8 rounded-3xl border-2 border-black">
                    <h3 class="text-xl font-bold mb-3">AI自動補完の精度はどのくらいですか？</h3>
                    <p class="text-gray-600 leading-relaxed">Free Dictionary APIとGoogle Gemini AIを組み合わせることで、高精度な単語情報を取得しています。ただし、補完結果は編集可能ですので、必要に応じて修正できます。</p>
                </div>
                <div class="bg-white p-8 rounded-3xl border-2 border-black">
                    <h3 class="text-xl font-bold mb-3">プランはいつでも変更できますか？</h3>
                    <p class="text-gray-600 leading-relaxed">はい、いつでもアップグレード・ダウングレードが可能です。（※現在フェーズ1のため、課金機能は準備中です）</p>
                </div>
                <div class="bg-white p-8 rounded-3xl border-2 border-black">
                    <h3 class="text-xl font-bold mb-3">スマホでも使えますか？</h3>
                    <p class="text-gray-600 leading-relaxed">はい、VocaBuddyは完全レスポンシブ対応です。スマートフォン、タブレット、PCのどのデバイスからでも快適にご利用いただけます。</p>
                </div>
                <div class="bg-white p-8 rounded-3xl border-2 border-black">
                    <h3 class="text-xl font-bold mb-3">登録した単語は他の人に見られますか？</h3>
                    <p class="text-gray-600 leading-relaxed">いいえ、登録した単語は完全にプライベートです。他のユーザーがあなたの単語帳を見ることはできません。</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 最終CTAセクション -->
    <section class="py-24 bg-black">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
            <h2 class="text-4xl md:text-6xl font-bold mb-6">
                今すぐ始めて、<br>英語学習を変えよう
            </h2>
            <p class="text-xl mb-10 leading-relaxed">
                20単語まで完全無料。クレジットカード不要。<br>
                30秒で登録完了。
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center mb-8">
                <a href="{{ route('register') }}" class="btn-primary px-12 py-4 rounded-full text-lg font-bold">
                    無料で始める
                </a>
                <a href="#pricing" class="bg-transparent border-2 border-white text-white px-12 py-4 rounded-full text-lg font-bold hover:bg-white hover:text-black transition">
                    プランを見る
                </a>
            </div>
            <p class="text-sm text-gray-400">
                すでにアカウントをお持ちですか？ <a href="{{ route('login') }}" class="text-yellow font-medium hover:underline">ログイン</a>
            </p>
        </div>
    </section>

    <!-- フッター -->
    <footer class="bg-black text-gray-400 py-12 border-t border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <div class="mb-6">
                    <a href="#" class="hover:text-white transition mx-4">プライバシーポリシー</a>
                    <a href="#" class="hover:text-white transition mx-4">利用規約</a>
                    <a href="#" class="hover:text-white transition mx-4">お問い合わせ</a>
                </div>
                <p class="text-sm">&copy; 2026 VocaBuddy. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
