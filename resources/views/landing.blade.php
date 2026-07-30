<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>VocaBuddy - AI搭載の英語学習アプリ</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Zen+Kaku+Gothic+New:wght@400;700;900&family=Gochi+Hand&display=swap" rel="stylesheet">
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

        .bg-black {
            background-color: var(--color-black);
        }

        .bg-white {
            background-color: var(--color-white);
        }

        /* ==============================
           Hero: 動くもやしヒーロー
           ============================== */
        .hero-section {
            display: flex;
            flex-direction: column;
            /* ロゴ+キャラの塊を縦中央に = キャラがY軸センターに来る */
            justify-content: center;
            /* 固定ナビ(h-16 = 4rem)を引いた画面いっぱいの高さ */
            min-height: calc(100vh - 4rem);
            min-height: calc(100dvh - 4rem);
        }

        .hero-logo {
            text-align: center;
            margin-bottom: clamp(1rem, 4vh, 3rem);
        }

        .hero-logo-gap {
            margin-left: clamp(0.5rem, 1.5vw, 1.25rem);
        }

        /* ==============================
           ヒーローに合わせた白黒手書きテイスト共通
           ============================== */
        .hand-label {
            font-family: 'Gochi Hand', cursive;
            font-size: clamp(1.5rem, 3.5vw, 2.2rem);
            display: inline-block;
            transform: rotate(-3deg);
        }

        .sketch-card {
            border: 2px solid #1A1A1A;
            border-radius: 1rem;
            background: #fff;
            box-shadow: 5px 5px 0 #1A1A1A;
        }

        .btn-ink {
            display: inline-block;
            background: #1A1A1A;
            color: #fff;
            font-weight: bold;
            padding: 1rem 2.5rem;
            border-radius: 9999px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 4px 4px 0 rgba(0, 0, 0, 0.25);
        }

        .btn-ink:hover {
            transform: translateY(-2px);
            box-shadow: 6px 6px 0 rgba(0, 0, 0, 0.25);
        }

        .hero-logo span {
            display: inline-block;
            font-weight: 900;
            font-size: clamp(2.6rem, 9vw, 6rem);
            letter-spacing: 0.02em;
            color: var(--color-black);
            animation: hero-pop-in 0.45s cubic-bezier(0.34, 1.56, 0.64, 1) both,
                       hero-sway 2.6s ease-in-out infinite;
            animation-delay: calc(var(--i) * 0.07s), calc(1s + var(--i) * 0.12s);
        }

        @keyframes hero-pop-in {
            0% { opacity: 0; transform: scale(0.2) rotate(-14deg); }
            100% { opacity: 1; transform: scale(1) rotate(0); }
        }

        @keyframes hero-sway {
            0%, 100% { transform: rotate(-2deg); }
            50% { transform: rotate(2deg); }
        }

        .hero-stage {
            position: relative;
            height: clamp(230px, 34vw, 380px);
            width: 100%;
            max-width: 80rem;
            margin: 0.5rem auto 0;
        }

        .mo {
            position: absolute;
            bottom: 0;
            cursor: pointer;
        }

        .mo img {
            width: 100%;
            display: block;
            mix-blend-mode: multiply;
            transform: rotate(var(--r));
            animation: mo-boil 0.5s steps(2, jump-none) infinite;
        }

        .mo img.flip {
            transform: scaleX(-1) rotate(var(--r));
            animation: mo-boil-f 0.5s steps(2, jump-none) infinite;
        }

        @keyframes mo-boil {
            0% { transform: rotate(var(--r)); }
            100% { transform: rotate(calc(var(--r) + 3deg)); }
        }

        @keyframes mo-boil-f {
            0% { transform: scaleX(-1) rotate(var(--r)); }
            100% { transform: scaleX(-1) rotate(calc(var(--r) + 3deg)); }
        }

        .mo:hover img:not(.flip), .mo.play img:not(.flip) {
            animation: mo-hop 0.45s ease-in-out infinite;
        }

        .mo:hover img.flip, .mo.play img.flip {
            animation: mo-hop-f 0.45s ease-in-out infinite;
        }

        @keyframes mo-hop {
            0%, 100% { transform: rotate(var(--r)) translateY(0); }
            40% { transform: rotate(calc(var(--r) * -1)) translateY(-26px) scale(1.06); }
        }

        @keyframes mo-hop-f {
            0%, 100% { transform: scaleX(-1) rotate(var(--r)) translateY(0); }
            40% { transform: scaleX(-1) rotate(calc(var(--r) * -1)) translateY(-26px) scale(1.06); }
        }

        .mo .say {
            position: absolute;
            left: 50%;
            width: var(--sayw);
            margin-left: calc(var(--sayw) / -2);
            top: calc(var(--sayw) * -0.55);
            opacity: 0;
            transform: scale(0.4);
            transition: all 0.18s cubic-bezier(0.34, 1.56, 0.64, 1);
            pointer-events: none;
        }

        .mo .say svg {
            width: 100%;
            height: auto;
        }

        .mo:hover .say, .mo.play .say {
            opacity: 1;
            transform: scale(1) rotate(-3deg);
        }

        /* スマホ: 5体 → 3体に減らして1体を大きく */
        .mo-sm { display: none; }

        @media (min-width: 768px) {
            .mo-sm { display: block; }
        }

        @media (prefers-reduced-motion: reduce) {
            .hero-logo span,
            .mo img,
            .mo:hover img,
            .mo.play img {
                animation: none !important;
                opacity: 1;
            }
        }

        /* Staggered Content Entrance */
        .content-hidden {
            opacity: 0;
        }

        .animate-fade-in {
            animation: fadeIn 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
            opacity: 0;
        }

        .animate-slide-up {
            animation: slideUp 1s cubic-bezier(0.4, 0, 0.2, 1) forwards;
            opacity: 0;
        }

        .animate-scale-in {
            animation: scaleIn 0.8s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
            opacity: 0;
        }

        .delay-100 { animation-delay: 0.1s; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-300 { animation-delay: 0.3s; }
        .delay-400 { animation-delay: 0.4s; }
        .delay-500 { animation-delay: 0.5s; }
        .delay-600 { animation-delay: 0.6s; }
        .delay-700 { animation-delay: 0.7s; }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        /* Respect reduced motion preference */
        @media (prefers-reduced-motion: reduce) {
            .animate-fade-in,
            .animate-slide-up,
            .animate-scale-in {
                animation: none;
                opacity: 1;
                transform: none;
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
            background-color: var(--color-black);
            color: var(--color-white);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 4px 6px 0 rgba(0, 0, 0, 0.25);
        }

        .btn-white {
            background-color: var(--color-white);
            color: var(--color-black);
            transition: transform 0.2s ease;
        }

        .btn-white:hover {
            transform: translateY(-2px);
        }

    </style>
</head>
<body class="antialiased">
    <x-side-menu></x-side-menu>
    <x-navigation></x-navigation>

    <!-- 手描き風吹き出し用のSVGフィルタ -->
    <svg width="0" height="0" style="position:absolute;" aria-hidden="true">
        <defs>
            <filter id="hero-wobble" x="-10%" y="-10%" width="120%" height="120%">
                <feTurbulence type="fractalNoise" baseFrequency="0.035" numOctaves="3" seed="7" result="noise"/>
                <feDisplacementMap in="SourceGraphic" in2="noise" scale="7" xChannelSelector="R" yChannelSelector="G"/>
            </filter>
        </defs>
    </svg>

    <!-- ヒーローセクション -->
    <section class="hero-section bg-white overflow-hidden">
        <h1 class="hero-logo" aria-label="JOIN US!">
            <span style="--i:0">J</span><span style="--i:1">O</span><span style="--i:2">I</span><span style="--i:3">N</span>
            <span style="--i:4" class="hero-logo-gap">U</span><span style="--i:5">S</span><span style="--i:6">!</span>
        </h1>

        <div class="hero-stage">
            <div class="mo" style="left:4%; width:clamp(120px, 16vw, 220px); --r:-8deg; --sayw:clamp(120px, 15vw, 160px);">
                <div class="say">
                    <svg viewBox="0 0 200 128">
                        <g filter="url(#hero-wobble)"><path d="M24,50 C20,28 50,12 98,12 C154,12 184,24 182,50 C180,72 150,84 112,85 L134,114 L100,85 C60,84 28,66 24,50 Z" fill="#fff" stroke="#1A1A1A" stroke-width="2.6" stroke-linejoin="round"/></g>
                        <text font-family="'Gochi Hand', cursive" font-size="38" fill="#1A1A1A" stroke="#fff" stroke-width="1.1"><tspan x="40" y="62" rotate="-10">w</tspan><tspan x="66" y="56" rotate="6">H</tspan><tspan x="92" y="60" rotate="-4">A</tspan><tspan x="116" y="54" rotate="8">T</tspan><tspan x="136" y="58" rotate="-6">!</tspan><tspan x="148" y="54" rotate="10">?</tspan></text>
                    </svg>
                </div>
                <img src="{{ asset('images/moyashi.jpg') }}" alt="もやしキャラクター">
            </div>

            <div class="mo mo-sm" style="left:26%; width:clamp(70px, 11vw, 150px); --r:6deg; bottom:14px; --sayw:clamp(90px, 11vw, 120px);">
                <div class="say">
                    <svg viewBox="0 0 200 120">
                        <g filter="url(#hero-wobble)"><path d="M24,50 C20,28 50,12 98,12 C154,12 184,24 182,50 C180,72 150,84 112,85 L134,110 L100,85 C60,84 28,66 24,50 Z" fill="#fff" stroke="#1A1A1A" stroke-width="2.8" stroke-linejoin="round"/></g>
                        <text font-family="'Gochi Hand', cursive" font-size="30" fill="#1A1A1A" stroke="#fff" stroke-width="1"><tspan x="60" y="60" rotate="-8">h</tspan><tspan x="82" y="55" rotate="5">i</tspan><tspan x="96" y="60" rotate="-4">!</tspan></text>
                    </svg>
                </div>
                <img src="{{ asset('images/moyashi.jpg') }}" alt="">
            </div>

            <div class="mo" style="left:42%; width:clamp(140px, 18vw, 250px); --r:3deg; --sayw:clamp(130px, 16vw, 170px);">
                <div class="say">
                    <svg viewBox="0 0 210 128">
                        <g filter="url(#hero-wobble)"><path d="M24,50 C20,28 50,12 100,12 C160,12 194,24 192,50 C190,72 156,84 116,85 L138,114 L104,85 C62,84 28,66 24,50 Z" fill="#fff" stroke="#1A1A1A" stroke-width="2.6" stroke-linejoin="round"/></g>
                        <text font-family="'Gochi Hand', cursive" font-size="30" fill="#1A1A1A" stroke="#fff" stroke-width="1"><tspan x="38" y="60" rotate="-8">s</tspan><tspan x="56" y="55" rotate="4">t</tspan><tspan x="72" y="60" rotate="-5">u</tspan><tspan x="92" y="54" rotate="7">d</tspan><tspan x="112" y="59" rotate="-4">y</tspan><tspan x="130" y="55" rotate="8">?</tspan></text>
                    </svg>
                </div>
                <img src="{{ asset('images/moyashi.jpg') }}" alt="" class="flip">
            </div>

            <div class="mo mo-sm" style="left:64%; width:clamp(60px, 10vw, 130px); --r:-12deg; bottom:26px; --sayw:clamp(85px, 10vw, 110px);">
                <div class="say">
                    <svg viewBox="0 0 200 120">
                        <g filter="url(#hero-wobble)"><path d="M24,50 C20,28 50,12 98,12 C154,12 184,24 182,50 C180,72 150,84 112,85 L134,110 L100,85 C60,84 28,66 24,50 Z" fill="#fff" stroke="#1A1A1A" stroke-width="2.8" stroke-linejoin="round"/></g>
                        <text font-family="'Gochi Hand', cursive" font-size="28" fill="#1A1A1A" stroke="#fff" stroke-width="1"><tspan x="42" y="58" rotate="-8">w</tspan><tspan x="66" y="54" rotate="5">o</tspan><tspan x="84" y="58" rotate="-4">w</tspan><tspan x="110" y="53" rotate="7">!</tspan></text>
                    </svg>
                </div>
                <img src="{{ asset('images/moyashi.jpg') }}" alt="">
            </div>

            <div class="mo" style="left:76%; width:clamp(100px, 14vw, 190px); --r:10deg; --sayw:clamp(95px, 12vw, 130px);">
                <div class="say">
                    <svg viewBox="0 0 200 120">
                        <g filter="url(#hero-wobble)"><path d="M24,50 C20,28 50,12 98,12 C154,12 184,24 182,50 C180,72 150,84 112,85 L134,110 L100,85 C60,84 28,66 24,50 Z" fill="#fff" stroke="#1A1A1A" stroke-width="2.8" stroke-linejoin="round"/></g>
                        <text font-family="'Gochi Hand', cursive" font-size="28" fill="#1A1A1A" stroke="#fff" stroke-width="1"><tspan x="36" y="58" rotate="-6">e</tspan><tspan x="54" y="54" rotate="5">a</tspan><tspan x="72" y="58" rotate="-4">s</tspan><tspan x="88" y="53" rotate="6">y</tspan><tspan x="104" y="58" rotate="-5">!</tspan></text>
                    </svg>
                </div>
                <img src="{{ asset('images/moyashi.jpg') }}" alt="" class="flip">
            </div>
        </div>
    </section>

    <!-- ② 特典バー -->
    <section class="py-12 bg-white">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="sketch-card p-8 md:p-10 text-center" style="transform: rotate(-0.5deg);">
                <span class="hand-label">free!</span>
                <h2 class="text-2xl md:text-3xl font-bold mt-2 mb-3">いまなら無料プランで全機能試せます</h2>
                <p class="text-gray-600 mb-6">クレジットカード不要・30秒で登録（仮テキスト）</p>
                <a href="{{ route('register') }}" class="btn-ink text-lg">無料で始める →</a>
            </div>
        </div>
    </section>

    <!-- ③ 読者の課題（共感） -->
    <section class="py-20 bg-white">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-3"><span class="hand-label">hmm...?</span></div>
            <h2 class="text-3xl md:text-5xl font-bold text-center mb-8">こんな経験、ありませんか？</h2>
            <p class="text-center text-lg md:text-xl leading-relaxed mb-14 text-gray-800">
                映画を観ていて「この単語いいな」と思った。<br>
                …3日後、思い出せない。<b>あの単語には、もう二度と出会えないかもしれない。</b>（仮テキスト）
            </p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="sketch-card p-8 text-center" style="transform: rotate(-1deg);">
                    <img src="{{ asset('images/moyashi.jpg') }}" alt="" class="w-16 mx-auto mb-4" style="mix-blend-mode: multiply; transform: rotate(-10deg);">
                    <span class="hand-label" style="font-size:1.3rem;">ugh...</span>
                    <h3 class="text-xl font-bold mb-3 mt-1">調べるのが面倒</h3>
                    <p class="text-gray-600 leading-relaxed">辞書で調べてノートに書いて…が面倒で、結局そのまま（仮）</p>
                </div>
                <div class="sketch-card p-8 text-center" style="transform: rotate(0.8deg);">
                    <img src="{{ asset('images/moyashi.jpg') }}" alt="" class="w-16 mx-auto mb-4" style="mix-blend-mode: multiply; transform: rotate(8deg);">
                    <span class="hand-label" style="font-size:1.3rem;">zzz...</span>
                    <h3 class="text-xl font-bold mb-3 mt-1">単語帳が続かない</h3>
                    <p class="text-gray-600 leading-relaxed">市販の単語帳は自分に関係ない単語ばかりで3日坊主（仮）</p>
                </div>
                <div class="sketch-card p-8 text-center" style="transform: rotate(-0.5deg);">
                    <img src="{{ asset('images/moyashi.jpg') }}" alt="" class="w-16 mx-auto mb-4" style="mix-blend-mode: multiply; transform: scaleX(-1) rotate(-6deg);">
                    <span class="hand-label" style="font-size:1.3rem;">oops!</span>
                    <h3 class="text-xl font-bold mb-3 mt-1">いざという時 出てこない</h3>
                    <p class="text-gray-600 leading-relaxed">覚えたはずなのに、外国人の友達への返信でパッと出てこない（仮）</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ④ 使うとどうなるか（未来） -->
    <section id="features" class="py-20 bg-white border-t border-black/10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-3"><span class="hand-label">your future!</span></div>
            <h2 class="text-3xl md:text-5xl font-bold text-center mb-4">VocaBuddyのある毎日</h2>
            <p class="text-center text-lg text-gray-600 mb-14">使い始めたあなたはこうなります（仮テキスト）</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                <div class="feature-card sketch-card p-10" style="transform: rotate(-0.6deg);">
                    <span class="hand-label">10 sec!</span>
                    <div class="inline-block bg-black text-white text-xs font-bold px-3 py-1 rounded-full ml-3 align-middle">AI自動補完</div>
                    <h3 class="text-2xl font-bold mt-4 mb-3">映画の途中でも、出会った単語が10秒であなたの単語帳に</h3>
                    <p class="text-gray-600 leading-relaxed">「あとで調べよう」が人生から消えます（仮）</p>
                </div>
                <div class="feature-card sketch-card p-10" style="transform: rotate(0.6deg);">
                    <span class="hand-label">quiz!</span>
                    <div class="inline-block bg-black text-white text-xs font-bold px-3 py-1 rounded-full ml-3 align-middle">単語テスト</div>
                    <h3 class="text-2xl font-bold mt-4 mb-3">通勤中の4択テストで、気づけば覚えている</h3>
                    <p class="text-gray-600 leading-relaxed">机に向かう「勉強」はもう不要（仮）</p>
                </div>
                <div class="feature-card sketch-card p-10" style="transform: rotate(0.4deg);">
                    <span class="hand-label">reply!</span>
                    <div class="inline-block bg-black text-white text-xs font-bold px-3 py-1 rounded-full ml-3 align-middle">AI返信アシスタント</div>
                    <h3 class="text-2xl font-bold mt-4 mb-3">外国人の友達への返信で、覚えた単語がスッと出てくる</h3>
                    <p class="text-gray-600 leading-relaxed">単語が「知識」から「会話できる英語」に変わる瞬間（仮）</p>
                </div>
                <div class="feature-card sketch-card p-10" style="transform: rotate(-0.4deg);">
                    <span class="hand-label">notes!</span>
                    <div class="inline-block bg-black text-white text-xs font-bold px-3 py-1 rounded-full ml-3 align-middle">学習ノート</div>
                    <h3 class="text-2xl font-bold mt-4 mb-3">学んだ文法や気づきが、ノートに貯まっていく</h3>
                    <p class="text-gray-600 leading-relaxed">1年後、あなただけの英語資産になります（仮）</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 単語帳デモセクション -->
    <section class="py-20 bg-white">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <div class="mb-3"><span class="hand-label">proof!</span></div>
                <h2 class="text-4xl md:text-5xl font-bold mb-4">
                    本当に10秒？ 見てください
                </h2>
                <p class="text-xl text-gray-600">AI自動補完で辞書を引く手間を削減（仮テキスト）</p>
            </div>

            <div id="word-demo-container" class="sketch-card rounded-3xl p-8 md:p-12">
                <div class="text-center mb-6">
                    <button id="word-demo-start-btn" class="bg-black hover:bg-gray-800 text-white px-8 py-4 rounded-full text-lg font-bold transition-all duration-300 transform hover:-translate-y-1 shadow-lg">
                        デモスタート
                    </button>
                </div>
                <div class="bg-white border-2 border-black rounded-2xl p-6 md:p-8 space-y-6">
                    <!-- 単語入力 -->
                    <div>
                        <label class="block text-sm font-bold text-black mb-2">英単語</label>
                        <div class="bg-gray-50 border-2 border-gray-300 rounded-xl px-5 py-3 min-h-[50px] flex items-center">
                            <p id="word-demo-input" class="text-gray-800 text-lg font-semibold"></p>
                        </div>
                    </div>

                    <!-- AI補完ボタン -->
                    <div class="text-center">
                        <button id="word-demo-button"
                            class="bg-black hover:bg-gray-800 text-white px-8 py-4 rounded-xl font-bold text-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                            <span id="word-demo-button-text">AIで意味を補完</span>
                        </button>
                    </div>

                    <!-- ローディング -->
                    <div id="word-demo-loading" class="hidden">
                        <div class="bg-white border-2 border-black rounded-xl p-6 text-center">
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-white mb-3">
                                <div class="w-8 h-8 border-4 border-gray-300 border-t-black rounded-full animate-spin"></div>
                            </div>
                            <p class="text-black font-semibold">AI が単語情報を取得中...</p>
                        </div>
                    </div>

                    <!-- 結果表示 -->
                    <div id="word-demo-result" class="hidden space-y-4">
                        <div class="bg-gray-50 border-2 border-black rounded-xl p-6">
                            <h4 class="text-lg font-bold text-black mb-4 flex items-center">
                                <svg class="w-6 h-6 mr-2 text-black" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                AI補完結果
                            </h4>

                            <div class="space-y-3">
                                <div class="bg-white rounded-xl p-4">
                                    <p class="text-sm text-gray-600 mb-1">発音</p>
                                    <p id="word-demo-pronunciation" class="text-lg font-semibold text-black"></p>
                                </div>
                                <div class="bg-white rounded-xl p-4">
                                    <p class="text-sm text-gray-600 mb-1">品詞</p>
                                    <p id="word-demo-pos" class="text-lg font-semibold text-black"></p>
                                </div>
                                <div class="bg-white rounded-xl p-4">
                                    <p class="text-sm text-gray-600 mb-1">意味</p>
                                    <p id="word-demo-meaning" class="text-lg text-black leading-relaxed"></p>
                                </div>
                                <div class="bg-white rounded-xl p-4">
                                    <p class="text-sm text-gray-600 mb-1">例文（英語）</p>
                                    <p id="word-demo-example-en" class="text-lg text-black leading-relaxed"></p>
                                </div>
                                <div class="bg-white rounded-xl p-4">
                                    <p class="text-sm text-gray-600 mb-1">例文（日本語）</p>
                                    <p id="word-demo-example-ja" class="text-lg text-black leading-relaxed"></p>
                                </div>
                            </div>
                        </div>

                        <!-- 登録ボタン -->
                        <div class="text-center pt-2">
                            <button id="word-demo-register"
                                class="bg-black hover:bg-gray-800 text-white px-8 py-3 rounded-xl font-bold text-lg transition-all duration-300 transform hover:-translate-y-1 disabled:opacity-50">
                                <span id="word-demo-register-text">単語帳に登録</span>
                            </button>
                        </div>

                        <!-- 登録完了 -->
                        <div id="word-demo-registered" class="hidden">
                            <div class="bg-green-50 border-2 border-green-500 rounded-xl p-4 text-center">
                                <p class="text-green-700 font-bold text-lg">✓ 単語帳に追加されました！</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 単語テストデモセクション -->
    <section class="py-20 bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <div class="mb-3"><span class="hand-label">try it!</span></div>
                <h2 class="text-4xl md:text-5xl font-bold mb-4">
                    単語テストで定着を確認
                </h2>
                <p class="text-xl text-gray-600">4択クイズで楽しく学習（仮テキスト）</p>
            </div>

            <div class="sketch-card rounded-3xl p-8 md:p-12">
                <div class="bg-white border-2 border-black rounded-2xl p-8 md:p-10">
                    <div class="text-center mb-8">
                        <div class="flex items-center justify-center mb-6">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-black">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <h2 class="text-xl font-semibold text-black mb-4">
                            この単語の意味は？
                        </h2>
                    </div>

                    <div class="my-12 text-center">
                        <div class="inline-block">
                            <p class="text-4xl md:text-5xl font-bold text-black tracking-wide">study</p>
                            <div class="h-1 bg-black mt-4 rounded-full"></div>
                        </div>
                    </div>

                    <div class="mb-10 bg-gray-100 rounded-xl p-6 border border-black">
                        <div class="flex items-center mb-3">
                            <svg class="w-5 h-5 mr-2 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                            </svg>
                            <span class="text-sm font-semibold text-black">例文</span>
                        </div>
                        <p class="text-black italic leading-relaxed">I need to study English for the exam.</p>
                    </div>

                    <div id="test-choices" class="space-y-4">
                        <button data-answer="A" data-correct="false" class="test-choice group w-full text-left bg-white hover:bg-gray-100 border-2 border-black hover:border-black rounded-xl p-5 transition-all duration-300 shadow-soft hover:shadow-soft-lg transform hover:-translate-y-0.5">
                            <div class="flex items-center">
                                <span class="choice-label inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-200 group-hover:bg-gray-300 text-black font-semibold mr-4 transition-colors flex-shrink-0">
                                    A
                                </span>
                                <span class="text-black font-medium">旅行する、旅する</span>
                            </div>
                        </button>

                        <button data-answer="B" data-correct="true" class="test-choice group w-full text-left bg-white hover:bg-gray-100 border-2 border-black hover:border-black rounded-xl p-5 transition-all duration-300 shadow-soft hover:shadow-soft-lg transform hover:-translate-y-0.5">
                            <div class="flex items-center">
                                <span class="choice-label inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-200 group-hover:bg-gray-300 text-black font-semibold mr-4 transition-colors flex-shrink-0">
                                    B
                                </span>
                                <span class="text-black font-medium">勉強する、研究する</span>
                            </div>
                        </button>

                        <button data-answer="C" data-correct="false" class="test-choice group w-full text-left bg-white hover:bg-gray-100 border-2 border-black hover:border-black rounded-xl p-5 transition-all duration-300 shadow-soft hover:shadow-soft-lg transform hover:-translate-y-0.5">
                            <div class="flex items-center">
                                <span class="choice-label inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-200 group-hover:bg-gray-300 text-black font-semibold mr-4 transition-colors flex-shrink-0">
                                    C
                                </span>
                                <span class="text-black font-medium">働く、仕事をする</span>
                            </div>
                        </button>

                        <button data-answer="D" data-correct="false" class="test-choice group w-full text-left bg-white hover:bg-gray-100 border-2 border-black hover:border-black rounded-xl p-5 transition-all duration-300 shadow-soft hover:shadow-soft-lg transform hover:-translate-y-0.5">
                            <div class="flex items-center">
                                <span class="choice-label inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-200 group-hover:bg-gray-300 text-black font-semibold mr-4 transition-colors flex-shrink-0">
                                    D
                                </span>
                                <span class="text-black font-medium">教える、指導する</span>
                            </div>
                        </button>
                    </div>

                    <!-- 結果表示 -->
                    <div id="test-result" class="hidden mt-6">
                        <div id="test-result-message" class="text-center p-6 rounded-xl border-2 mb-4">
                            <p class="text-xl font-bold mb-2"></p>
                            <p class="text-sm"></p>
                        </div>
                        <button id="test-retry-btn" class="w-full bg-black hover:bg-gray-800 text-white px-6 py-4 rounded-xl font-bold transition-all duration-300 transform hover:-translate-y-1">
                            もう一度挑戦
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- AI返信デモセクション -->
    <section class="py-20 bg-white">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <div class="mb-3"><span class="hand-label">see this!</span></div>
                <h2 class="text-4xl md:text-5xl font-bold mb-4">
                    AI返信アシスタント体験
                </h2>
                <p class="text-xl text-gray-600">実際の動作を見てみましょう（仮テキスト）</p>
            </div>

            <div id="reply-demo-container" class="sketch-card rounded-3xl p-8 md:p-12">
                <div class="text-center mb-6">
                    <button id="reply-demo-start-btn" class="bg-black hover:bg-gray-800 text-white px-8 py-4 rounded-full text-lg font-bold transition-all duration-300 transform hover:-translate-y-1 shadow-lg">
                        デモスタート
                    </button>
                </div>
                <div class="bg-white border-2 border-black rounded-2xl p-6 md:p-8 space-y-6">
                    <!-- 英文入力 -->
                    <div class="relative">
                        <div id="demo-step-1" class="hidden mb-3">
                            <span class="inline-block bg-white border-2 border-black text-black px-4 py-2 rounded-full text-sm font-bold shadow-lg animate-bounce">
                                ステップ1：相手からの英文を入力
                            </span>
                        </div>
                        <label class="block text-sm font-bold text-black mb-2">友達からのメッセージ</label>
                        <div class="bg-gray-50 border-2 border-gray-300 rounded-xl px-5 py-3 min-h-[60px] flex items-center">
                            <p id="demo-english" class="text-gray-800 text-lg"></p>
                        </div>
                    </div>

                    <!-- カテゴリー選択 -->
                    <div class="relative">
                        <div id="demo-step-2" class="hidden mb-3">
                            <span class="inline-block bg-white border-2 border-black text-black px-4 py-2 rounded-full text-sm font-bold shadow-lg animate-bounce">
                                ステップ2：カテゴリを選択
                            </span>
                        </div>
                        <label class="block text-sm font-bold text-black mb-3">相手との関係性</label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <div id="demo-category-friend" class="bg-white border-2 border-gray-300 rounded-xl px-4 py-3 transition-all duration-200 opacity-50">
                                <div class="flex flex-col items-center">
                                    <svg class="w-6 h-6 mb-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    <span class="text-sm font-semibold text-gray-400">友人</span>
                                </div>
                            </div>
                            <div id="demo-category-work" class="bg-white border-2 border-gray-300 rounded-xl px-4 py-3 transition-all duration-200 opacity-50">
                                <div class="flex flex-col items-center">
                                    <svg class="w-6 h-6 mb-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    <span class="text-sm font-semibold text-gray-400">仕事</span>
                                </div>
                            </div>
                            <div id="demo-category-romantic" class="bg-white border-2 border-gray-300 rounded-xl px-4 py-3 transition-all duration-200 opacity-50">
                                <div class="flex flex-col items-center">
                                    <svg class="w-6 h-6 mb-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                    </svg>
                                    <span class="text-sm font-semibold text-gray-400">恋人</span>
                                </div>
                            </div>
                            <div id="demo-category-family" class="bg-white border-2 border-gray-300 rounded-xl px-4 py-3 transition-all duration-200 opacity-50">
                                <div class="flex flex-col items-center">
                                    <svg class="w-6 h-6 mb-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                    </svg>
                                    <span class="text-sm font-semibold text-gray-400">家族</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 日本語入力 -->
                    <div class="relative">
                        <div id="demo-step-3" class="hidden mb-3">
                            <span class="inline-block bg-white border-2 border-black text-black px-4 py-2 rounded-full text-sm font-bold shadow-lg animate-bounce">
                                ステップ3：返信したい内容を日本語で入力
                            </span>
                        </div>
                        <label class="block text-sm font-bold text-black mb-2">返信したい内容（日本語）</label>
                        <div class="bg-gray-50 border-2 border-gray-300 rounded-xl px-5 py-3 min-h-[60px] flex items-center">
                            <p id="demo-japanese" class="text-gray-800 text-lg"></p>
                        </div>
                    </div>

                    <!-- AI生成ボタン -->
                    <div class="relative text-center">
                        <div id="demo-step-4" class="hidden mb-3 flex justify-center">
                            <span class="inline-block bg-white border-2 border-black text-black px-4 py-2 rounded-full text-sm font-bold shadow-lg animate-bounce">
                                ステップ4：AIが文章作成
                            </span>
                        </div>
                        <button id="demo-button"
                            class="bg-black hover:bg-gray-800 text-white px-8 py-4 rounded-xl font-bold text-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                            <span id="demo-button-text">AI返信を生成</span>
                        </button>
                    </div>

                    <!-- ローディング -->
                    <div id="demo-loading" class="hidden">
                        <div class="bg-white border-2 border-black rounded-xl p-6 text-center">
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-white mb-3">
                                <div class="w-8 h-8 border-4 border-gray-300 border-t-black rounded-full animate-spin"></div>
                            </div>
                            <p class="text-black font-semibold">AI が返信文を生成中...</p>
                        </div>
                    </div>

                    <!-- 結果表示 -->
                    <div id="demo-result" class="hidden">
                        <div class="bg-gray-50 border-2 border-black rounded-xl p-6">
                            <h4 class="text-lg font-bold text-black mb-3 flex items-center">
                                <svg class="w-6 h-6 mr-2 text-black" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                生成された返信文
                            </h4>
                            <div class="bg-white rounded-xl p-4 mb-3">
                                <p id="demo-result-text" class="text-gray-800 text-lg leading-relaxed"></p>
                            </div>
                            <p class="text-sm text-gray-600">💡 あなたが登録した単語を使った自然な英語の返信文を生成します</p>
                        </div>
                    </div>

                    <!-- 使用された単語 -->
                    <div id="demo-used-words" class="hidden">
                        <div class="bg-white border-2 border-black rounded-xl p-6">
                            <h4 class="text-sm font-bold text-black mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                使用された単語帳の単語
                            </h4>
                            <div class="bg-white border-2 border-black rounded-xl p-5">
                                <p class="font-bold text-black text-lg mb-2">study</p>
                                <div class="space-y-1">
                                    <p class="text-sm text-black">・勉強する、研究する</p>
                                    <p class="text-sm text-black">・勉強、研究</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

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
    <section id="pricing" class="py-20 bg-white border-t border-black/10">
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
                    <div class="inline-block px-3 py-1 bg-black rounded-full text-xs font-bold text-white mb-4">
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
                    <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-black flex items-center justify-center text-2xl font-bold text-white">1</div>
                    <h3 class="text-xl font-bold mb-3">無料で新規登録</h3>
                    <p class="text-gray-600 leading-relaxed">メールアドレスとパスワードだけで、30秒で登録完了</p>
                </div>
                <div class="text-center">
                    <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-black flex items-center justify-center text-2xl font-bold text-white">2</div>
                    <h3 class="text-xl font-bold mb-3">単語を登録</h3>
                    <p class="text-gray-600 leading-relaxed">英単語を入力すると、AIが自動で発音・意味を補完</p>
                </div>
                <div class="text-center">
                    <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-black flex items-center justify-center text-2xl font-bold text-white">3</div>
                    <h3 class="text-xl font-bold mb-3">テスト・返信で活用</h3>
                    <p class="text-gray-600 leading-relaxed">単語テストで定着を確認、返信アシスタントで実践</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ユーザーの声セクション -->
    <section class="py-20 bg-white border-t border-black/10">
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
                <a href="{{ route('register') }}" class="bg-white text-black hover:bg-gray-200 px-12 py-4 rounded-full text-lg font-bold transition">
                    無料で始める
                </a>
                <a href="#pricing" class="bg-transparent border-2 border-white text-white px-12 py-4 rounded-full text-lg font-bold hover:bg-white hover:text-black transition">
                    プランを見る
                </a>
            </div>
            <p class="text-sm text-gray-400">
                すでにアカウントをお持ちですか？ <a href="{{ route('login') }}" class="text-white font-bold underline">ログイン</a>
            </p>
        </div>
    </section>

    <!-- ⑦ 追伸 -->
    <section class="py-20 bg-white">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <span class="hand-label">p.s.</span>
            <div class="sketch-card p-8 md:p-12 mt-4 text-left" style="transform: rotate(0.5deg);">
                <p class="leading-loose text-gray-800">
                    私自身、映画で出会った単語を何度も忘れてきました。<br>
                    「あのとき出会った単語をぜんぶ覚えていたら、今ごろどれだけ話せただろう」——<br>
                    そんな悔しさから、このアプリを一人で作りました。<br>
                    あなたが明日出会う単語は、もう逃がしません。（仮テキスト）
                </p>
                <div class="flex items-center justify-end mt-8 gap-3">
                    <p class="font-bold">— 開発者より</p>
                    <img src="{{ asset('images/moyashi.jpg') }}" alt="もやしキャラクター" class="w-14" style="mix-blend-mode: multiply; transform: rotate(8deg);">
                </div>
            </div>
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

    <script>
        // 単語帳デモアニメーション
        const wordDemoData = {
            word: "study",
            pronunciation: "スタディ",
            pos: "動詞/名詞",
            meaning: "勉強する、研究する / 勉強、研究",
            exampleEn: "I need to study English for the exam.",
            exampleJa: "試験のために英語を勉強する必要があります。"
        };

        let isWordAnimating = false;
        let wordAutoReplayTimeout;

        async function typeTextWord(element, text, speed = 50) {
            element.textContent = '';
            for (let i = 0; i < text.length; i++) {
                element.textContent += text[i];
                await new Promise(resolve => setTimeout(resolve, speed));
            }
        }

        async function runWordDemo() {
            if (isWordAnimating) return;
            isWordAnimating = true;

            const inputEl = document.getElementById('word-demo-input');
            const buttonEl = document.getElementById('word-demo-button');
            const buttonTextEl = document.getElementById('word-demo-button-text');
            const loadingEl = document.getElementById('word-demo-loading');
            const resultEl = document.getElementById('word-demo-result');
            const pronunciationEl = document.getElementById('word-demo-pronunciation');
            const posEl = document.getElementById('word-demo-pos');
            const meaningEl = document.getElementById('word-demo-meaning');
            const exampleEnEl = document.getElementById('word-demo-example-en');
            const exampleJaEl = document.getElementById('word-demo-example-ja');
            const registerBtn = document.getElementById('word-demo-register');
            const registerTextEl = document.getElementById('word-demo-register-text');
            const registeredEl = document.getElementById('word-demo-registered');

            // リセット
            inputEl.textContent = '';
            loadingEl.classList.add('hidden');
            resultEl.classList.add('hidden');
            registeredEl.classList.add('hidden');
            pronunciationEl.textContent = '';
            posEl.textContent = '';
            meaningEl.textContent = '';
            exampleEnEl.textContent = '';
            exampleJaEl.textContent = '';
            buttonEl.disabled = true;
            buttonTextEl.textContent = '入力中...';
            registerBtn.disabled = true;
            registerBtn.style.display = 'block';

            // 単語をタイピング
            await typeTextWord(inputEl, wordDemoData.word, 100);
            await new Promise(resolve => setTimeout(resolve, 800));

            // ボタンを有効化
            buttonEl.disabled = false;
            buttonTextEl.textContent = 'AIで意味を補完';

            // 自動クリック
            await new Promise(resolve => setTimeout(resolve, 500));
            buttonEl.disabled = true;
            buttonTextEl.textContent = '取得中...';

            // ローディング表示
            loadingEl.classList.remove('hidden');
            await new Promise(resolve => setTimeout(resolve, 1500));

            // ローディング非表示
            loadingEl.classList.add('hidden');

            // 結果を表示
            resultEl.classList.remove('hidden');
            await new Promise(resolve => setTimeout(resolve, 200));

            pronunciationEl.textContent = wordDemoData.pronunciation;
            await new Promise(resolve => setTimeout(resolve, 300));

            posEl.textContent = wordDemoData.pos;
            await new Promise(resolve => setTimeout(resolve, 300));

            await typeTextWord(meaningEl, wordDemoData.meaning, 40);
            await new Promise(resolve => setTimeout(resolve, 300));

            // 例文（英語）を表示
            await typeTextWord(exampleEnEl, wordDemoData.exampleEn, 30);
            await new Promise(resolve => setTimeout(resolve, 300));

            // 例文（日本語）を表示
            await typeTextWord(exampleJaEl, wordDemoData.exampleJa, 40);
            await new Promise(resolve => setTimeout(resolve, 800));

            // 登録ボタン有効化
            registerBtn.disabled = false;
            registerTextEl.textContent = '単語帳に登録';

            // 自動クリック
            await new Promise(resolve => setTimeout(resolve, 800));
            registerBtn.disabled = true;
            registerTextEl.textContent = '登録中...';
            await new Promise(resolve => setTimeout(resolve, 500));

            // 登録完了（登録ボタンを非表示にする）
            registeredEl.classList.remove('hidden');
            registerBtn.style.display = 'none';

            // 完了
            buttonEl.disabled = false;
            buttonTextEl.textContent = 'もう一度見る';
            isWordAnimating = false;

            // デモスタートボタンを再度有効化
            const startBtn = document.getElementById('word-demo-start-btn');
            startBtn.disabled = false;
            startBtn.textContent = 'デモスタート';
        }

        // 単語デモスタートボタンクリックイベント
        document.getElementById('word-demo-start-btn').addEventListener('click', () => {
            const startBtn = document.getElementById('word-demo-start-btn');
            startBtn.disabled = true;
            startBtn.textContent = 'アニメーション実行中...';
            clearTimeout(wordAutoReplayTimeout);
            runWordDemo();
        });

        // 単語デモボタンクリックイベント
        document.getElementById('word-demo-button').addEventListener('click', () => {
            const startBtn = document.getElementById('word-demo-start-btn');
            startBtn.disabled = true;
            startBtn.textContent = 'アニメーション実行中...';
            clearTimeout(wordAutoReplayTimeout);
            runWordDemo();
        });

        document.getElementById('word-demo-register').addEventListener('click', () => {
            const startBtn = document.getElementById('word-demo-start-btn');
            startBtn.disabled = true;
            startBtn.textContent = 'アニメーション実行中...';
            clearTimeout(wordAutoReplayTimeout);
            runWordDemo();
        });

        // Intersection Observerで画面外に出たら停止
        const wordDemoSection = document.querySelector('#word-demo-button')?.closest('section');
        const wordObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) {
                    // 画面外に出たらアニメーションを停止
                    clearTimeout(wordAutoReplayTimeout);
                    isWordAnimating = false;

                    // UI要素をリセット
                    const inputEl = document.getElementById('word-demo-input');
                    const buttonEl = document.getElementById('word-demo-button');
                    const buttonTextEl = document.getElementById('word-demo-button-text');
                    const loadingEl = document.getElementById('word-demo-loading');
                    const resultEl = document.getElementById('word-demo-result');
                    const registeredEl = document.getElementById('word-demo-registered');
                    const registerBtn = document.getElementById('word-demo-register');
                    const startBtn = document.getElementById('word-demo-start-btn');

                    if (inputEl) inputEl.textContent = '';
                    if (loadingEl) loadingEl.classList.add('hidden');
                    if (resultEl) resultEl.classList.add('hidden');
                    if (registeredEl) registeredEl.classList.add('hidden');
                    if (buttonEl) {
                        buttonEl.disabled = false;
                        buttonTextEl.textContent = 'AIで意味を補完';
                    }
                    if (registerBtn) {
                        registerBtn.disabled = true;
                        registerBtn.style.display = 'block';
                    }
                    if (startBtn) {
                        startBtn.disabled = false;
                        startBtn.textContent = 'デモスタート';
                    }
                }
            });
        }, { threshold: 0.3 });

        if (wordDemoSection) {
            wordObserver.observe(wordDemoSection);
        }

        // AI返信デモアニメーション
        const demoData = {
            english: "How's your English learning going? Are you still practicing?",
            japanese: "うん！最近は毎日英語を勉強しているよ。試験に向けて頑張ってる。",
            result: "Yes! I've been studying English every day recently. I'm working hard for the exam. I need to study more vocabulary and grammar!"
        };

        let isAnimating = false;
        let autoReplayTimeout;

        // タイピングアニメーション関数
        async function typeText(element, text, speed = 50) {
            element.textContent = '';
            for (let i = 0; i < text.length; i++) {
                element.textContent += text[i];
                await new Promise(resolve => setTimeout(resolve, speed));
            }
        }

        // デモアニメーション実行
        async function runDemo() {
            if (isAnimating) return;
            isAnimating = true;

            const englishEl = document.getElementById('demo-english');
            const japaneseEl = document.getElementById('demo-japanese');
            const buttonEl = document.getElementById('demo-button');
            const buttonTextEl = document.getElementById('demo-button-text');
            const loadingEl = document.getElementById('demo-loading');
            const resultEl = document.getElementById('demo-result');
            const resultTextEl = document.getElementById('demo-result-text');
            const usedWordsEl = document.getElementById('demo-used-words');
            const categoryFriend = document.getElementById('demo-category-friend');
            const friendIcon = categoryFriend.querySelector('svg');
            const friendText = categoryFriend.querySelector('span');
            const step1 = document.getElementById('demo-step-1');
            const step2 = document.getElementById('demo-step-2');
            const step3 = document.getElementById('demo-step-3');
            const step4 = document.getElementById('demo-step-4');

            // リセット
            englishEl.textContent = '';
            japaneseEl.textContent = '';
            loadingEl.classList.add('hidden');
            resultEl.classList.add('hidden');
            resultTextEl.textContent = '';
            usedWordsEl.classList.add('hidden');
            buttonEl.disabled = true;
            buttonTextEl.textContent = '入力中...';

            // ステップバッジをリセット
            step1.classList.add('hidden');
            step2.classList.add('hidden');
            step3.classList.add('hidden');
            step4.classList.add('hidden');

            // カテゴリーをリセット
            categoryFriend.classList.remove('bg-gray-100', 'border-black', 'opacity-100');
            categoryFriend.classList.add('border-gray-300', 'opacity-50');
            if (friendIcon) {
                friendIcon.classList.remove('text-black');
                friendIcon.classList.add('text-gray-400');
            }
            if (friendText) {
                friendText.classList.remove('text-black');
                friendText.classList.add('text-gray-400');
            }

            // ステップ1を表示
            step1.classList.remove('hidden');
            await new Promise(resolve => setTimeout(resolve, 800));

            // 英文をタイピング
            await typeText(englishEl, demoData.english, 30);
            await new Promise(resolve => setTimeout(resolve, 500));

            // ステップ1を非表示、ステップ2を表示
            step1.classList.add('hidden');
            step2.classList.remove('hidden');
            await new Promise(resolve => setTimeout(resolve, 600));

            // カテゴリー「友人」を選択
            categoryFriend.classList.remove('border-gray-300', 'opacity-50');
            categoryFriend.classList.add('bg-gray-100', 'border-black', 'opacity-100');
            if (friendIcon) {
                friendIcon.classList.remove('text-gray-400');
                friendIcon.classList.add('text-black');
            }
            if (friendText) {
                friendText.classList.remove('text-gray-400');
                friendText.classList.add('text-black');
            }
            await new Promise(resolve => setTimeout(resolve, 500));

            // ステップ2を非表示、ステップ3を表示
            step2.classList.add('hidden');
            step3.classList.remove('hidden');
            await new Promise(resolve => setTimeout(resolve, 600));

            // 日本語をタイピング
            await typeText(japaneseEl, demoData.japanese, 50);
            await new Promise(resolve => setTimeout(resolve, 800));

            // ステップ3を非表示
            step3.classList.add('hidden');

            // ボタンを有効化
            buttonEl.disabled = false;
            buttonTextEl.textContent = 'AI返信を生成';

            // ステップ4を表示
            await new Promise(resolve => setTimeout(resolve, 500));
            step4.classList.remove('hidden');
            await new Promise(resolve => setTimeout(resolve, 800));

            // 自動クリック
            buttonEl.disabled = true;
            buttonTextEl.textContent = '生成中...';

            // ローディング表示
            loadingEl.classList.remove('hidden');
            await new Promise(resolve => setTimeout(resolve, 2000));

            // ローディング非表示とステップ4を非表示
            loadingEl.classList.add('hidden');
            step4.classList.add('hidden');

            // 結果を表示
            resultEl.classList.remove('hidden');
            await typeText(resultTextEl, demoData.result, 40);

            // 返信文のタイピング完了後、少し待機してから使用単語を表示
            await new Promise(resolve => setTimeout(resolve, 800));
            usedWordsEl.classList.remove('hidden');

            // 完了
            buttonEl.disabled = false;
            buttonTextEl.textContent = 'もう一度見る';
            isAnimating = false;

            // デモスタートボタンを再度有効化
            const startBtn = document.getElementById('reply-demo-start-btn');
            startBtn.disabled = false;
            startBtn.textContent = 'デモスタート';
        }

        // AI返信デモスタートボタンクリックイベント
        document.getElementById('reply-demo-start-btn').addEventListener('click', () => {
            const startBtn = document.getElementById('reply-demo-start-btn');
            startBtn.disabled = true;
            startBtn.textContent = 'アニメーション実行中...';
            clearTimeout(autoReplayTimeout);
            runDemo();
        });

        // ボタンクリックイベント
        document.getElementById('demo-button').addEventListener('click', () => {
            const startBtn = document.getElementById('reply-demo-start-btn');
            startBtn.disabled = true;
            startBtn.textContent = 'アニメーション実行中...';
            clearTimeout(autoReplayTimeout);
            runDemo();
        });

        // Intersection Observerで画面外に出たら停止
        const demoSection = document.querySelector('#demo-button')?.closest('section');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) {
                    // 画面外に出たらアニメーションを停止
                    clearTimeout(autoReplayTimeout);
                    isAnimating = false;

                    // UI要素をリセット
                    const englishEl = document.getElementById('demo-english');
                    const japaneseEl = document.getElementById('demo-japanese');
                    const buttonEl = document.getElementById('demo-button');
                    const buttonTextEl = document.getElementById('demo-button-text');
                    const loadingEl = document.getElementById('demo-loading');
                    const resultEl = document.getElementById('demo-result');
                    const usedWordsEl = document.getElementById('demo-used-words');
                    const categoryFriend = document.getElementById('demo-category-friend');
                    const step1 = document.getElementById('demo-step-1');
                    const step2 = document.getElementById('demo-step-2');
                    const step3 = document.getElementById('demo-step-3');
                    const step4 = document.getElementById('demo-step-4');

                    if (englishEl) englishEl.textContent = '';
                    if (japaneseEl) japaneseEl.textContent = '';
                    if (loadingEl) loadingEl.classList.add('hidden');
                    if (resultEl) resultEl.classList.add('hidden');
                    if (usedWordsEl) usedWordsEl.classList.add('hidden');
                    if (step1) step1.classList.add('hidden');
                    if (step2) step2.classList.add('hidden');
                    if (step3) step3.classList.add('hidden');
                    if (step4) step4.classList.add('hidden');
                    if (categoryFriend) {
                        categoryFriend.classList.remove('bg-gray-100', 'border-black', 'opacity-100');
                        categoryFriend.classList.add('border-gray-300', 'opacity-50');
                        const friendIcon = categoryFriend.querySelector('svg');
                        const friendText = categoryFriend.querySelector('span');
                        if (friendIcon) {
                            friendIcon.classList.remove('text-black');
                            friendIcon.classList.add('text-gray-400');
                        }
                        if (friendText) {
                            friendText.classList.remove('text-black');
                            friendText.classList.add('text-gray-400');
                        }
                    }
                    if (buttonEl) {
                        buttonEl.disabled = false;
                        buttonTextEl.textContent = 'AI返信を生成';
                    }
                    const startBtn = document.getElementById('reply-demo-start-btn');
                    if (startBtn) {
                        startBtn.disabled = false;
                        startBtn.textContent = 'デモスタート';
                    }
                }
            });
        }, { threshold: 0.3 });

        if (demoSection) {
            observer.observe(demoSection);
        }

        // 単語テストデモ
        let testAnswered = false;

        // 選択肢クリックイベント
        document.querySelectorAll('.test-choice').forEach(button => {
            button.addEventListener('click', (e) => {
                if (testAnswered) return; // 既に回答済みの場合は無視
                testAnswered = true;

                const clickedButton = e.currentTarget;
                const isCorrect = clickedButton.dataset.correct === 'true';
                const resultDiv = document.getElementById('test-result');
                const resultMessage = document.getElementById('test-result-message');

                // 全ての選択肢を無効化
                document.querySelectorAll('.test-choice').forEach(btn => {
                    btn.disabled = true;
                    btn.classList.remove('hover:bg-gray-100', 'hover:border-black', 'hover:shadow-soft-lg', 'transform', 'hover:-translate-y-0.5');
                });

                if (isCorrect) {
                    // 正解の場合
                    clickedButton.classList.remove('border-black');
                    clickedButton.classList.add('border-green-500', 'bg-green-50');
                    clickedButton.querySelector('.choice-label').classList.remove('bg-gray-200');
                    clickedButton.querySelector('.choice-label').classList.add('bg-green-500', 'text-white');

                    resultMessage.classList.remove('border-red-500', 'bg-red-50');
                    resultMessage.classList.add('border-green-500', 'bg-green-50');
                    resultMessage.querySelector('p:first-child').textContent = '🎉 正解です！';
                    resultMessage.querySelector('p:first-child').classList.add('text-green-700');
                    resultMessage.querySelector('p:last-child').textContent = 'studyは「勉強する、研究する」という意味です';
                    resultMessage.querySelector('p:last-child').classList.add('text-green-600');
                } else {
                    // 不正解の場合
                    clickedButton.classList.remove('border-black');
                    clickedButton.classList.add('border-red-500', 'bg-red-50');
                    clickedButton.querySelector('.choice-label').classList.remove('bg-gray-200');
                    clickedButton.querySelector('.choice-label').classList.add('bg-red-500', 'text-white');

                    // 正解を緑色でハイライト
                    document.querySelectorAll('.test-choice').forEach(btn => {
                        if (btn.dataset.correct === 'true') {
                            btn.classList.remove('border-black');
                            btn.classList.add('border-green-500', 'bg-green-50');
                            btn.querySelector('.choice-label').classList.remove('bg-gray-200');
                            btn.querySelector('.choice-label').classList.add('bg-green-500', 'text-white');
                        }
                    });

                    resultMessage.classList.remove('border-green-500', 'bg-green-50');
                    resultMessage.classList.add('border-red-500', 'bg-red-50');
                    resultMessage.querySelector('p:first-child').textContent = '❌ 残念...';
                    resultMessage.querySelector('p:first-child').classList.add('text-red-700');
                    resultMessage.querySelector('p:last-child').textContent = '正解は「勉強する、研究する」でした';
                    resultMessage.querySelector('p:last-child').classList.add('text-red-600');
                }

                resultDiv.classList.remove('hidden');
            });
        });

        // もう一度挑戦ボタン
        document.getElementById('test-retry-btn').addEventListener('click', () => {
            resetTestDemo();
        });

        function resetTestDemo() {
            testAnswered = false;
            const resultDiv = document.getElementById('test-result');
            const resultMessage = document.getElementById('test-result-message');

            resultDiv.classList.add('hidden');

            // 全ての選択肢をリセット
            document.querySelectorAll('.test-choice').forEach(btn => {
                btn.disabled = false;
                btn.classList.remove('border-red-500', 'bg-red-50', 'border-green-500', 'bg-green-50');
                btn.classList.add('border-black', 'hover:bg-gray-100', 'hover:border-black', 'hover:shadow-soft-lg', 'transform', 'hover:-translate-y-0.5');

                const label = btn.querySelector('.choice-label');
                label.classList.remove('bg-red-500', 'bg-green-500', 'text-white');
                label.classList.add('bg-gray-200');
            });

            // 結果メッセージのクラスをクリア
            resultMessage.classList.remove('border-red-500', 'bg-red-50', 'border-green-500', 'bg-green-50');
            resultMessage.querySelector('p:first-child').classList.remove('text-red-700', 'text-green-700');
            resultMessage.querySelector('p:last-child').classList.remove('text-red-600', 'text-green-600');
        }

        // ====================================
        // ヒーロー: 吹き出しは自動で順番に表示 + ホバー/タップでも表示
        // ====================================
        const heroMos = Array.from(document.querySelectorAll('.mo'));

        function playMo(mo, duration = 1400) {
            mo.classList.add('play');
            clearTimeout(mo._playTimer);
            mo._playTimer = setTimeout(() => mo.classList.remove('play'), duration);
        }

        // タッチ端末: タップで跳ねる+吹き出し
        heroMos.forEach(mo => {
            mo.addEventListener('touchstart', () => playMo(mo), { passive: true });
        });

        // 自動巡回: 左から順番に1体ずつ吹き出しを出す（reduced-motion時は無効）
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            let heroMoIndex = 0;
            const cycleMo = () => {
                // スマホで非表示のキャラはスキップ
                const visibleMos = heroMos.filter(mo => mo.offsetParent !== null);
                if (visibleMos.length === 0) return;
                playMo(visibleMos[heroMoIndex % visibleMos.length]);
                heroMoIndex++;
            };
            cycleMo(); // 待ち時間なしで1体目から開始
            setInterval(cycleMo, 1500);
        }
    </script>
</body>
</html>
