<x-template title="返信アシスタント">
    <div class="min-h-screen bg-gradient-to-br from-primary-50 via-white to-accent-50">
        <section class="py-16 px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto">
                <div class="mb-10">
                    <a href="{{route('words.index')}}" class="inline-flex items-center text-sm text-primary-600 hover:text-primary-900 font-medium transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        単語帳に戻る
                    </a>
                </div>

                <div class="bg-white/80 backdrop-blur-sm shadow-soft-lg rounded-2xl p-10 border border-primary-100">
                    <div class="text-center mb-10">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-br from-accent-400 to-accent-600 mb-4">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                            </svg>
                        </div>
                        <h2 class="text-3xl font-bold text-primary-900 mb-3">
                            返信アシスタント
                        </h2>
                        <p class="text-primary-600 leading-relaxed max-w-xl mx-auto">
                            友達からのメッセージとあなたの返信意図を入力すると、<br>学習中の単語を活用した自然な返信文を提案します。
                        </p>
                    </div>

                    @if(session('error'))
                        <div class="mb-8 bg-red-50/80 backdrop-blur-sm border border-red-200 text-red-700 px-6 py-4 rounded-xl shadow-soft">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                <span>{{ session('error') }}</span>
                            </div>
                        </div>
                    @endif

                    <form method="post" action="{{route('GenerateReply')}}" class="space-y-8" id="replyForm" onsubmit="showLoading()">
                        @csrf

                        <div class="space-y-2">
                            <label for="friend_message" class="block text-sm font-semibold text-primary-900">
                                友達からのメッセージ（英語）
                            </label>
                            <textarea id="friend_message" name="friend_message" required
                                placeholder="例: Hey! How have you been? I haven't seen you in a while."
                                class="w-full border-2 border-primary-200 rounded-xl px-5 py-4 focus:outline-none focus:border-accent-500 focus:ring-4 focus:ring-accent-100 resize-none transition-all duration-200 bg-white/50 backdrop-blur-sm text-primary-900 placeholder-primary-400"
                                rows="5"></textarea>
                        </div>

                        <div class="space-y-3">
                            <label class="block text-sm font-semibold text-primary-900">
                                相手との関係性 <span class="text-red-600">*</span>
                            </label>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                <label class="relative flex items-center justify-center cursor-pointer">
                                    <input type="radio" name="relationship" value="friend" required class="peer sr-only">
                                    <div class="w-full bg-white border-2 border-primary-200 peer-checked:border-accent-500 peer-checked:bg-accent-50 rounded-xl px-4 py-3 transition-all duration-200 peer-checked:shadow-soft">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-6 h-6 mb-1 text-primary-600 peer-checked:text-accent-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            </svg>
                                            <span class="text-sm font-semibold text-primary-900">友人</span>
                                        </div>
                                    </div>
                                </label>
                                <label class="relative flex items-center justify-center cursor-pointer">
                                    <input type="radio" name="relationship" value="work" required class="peer sr-only">
                                    <div class="w-full bg-white border-2 border-primary-200 peer-checked:border-accent-500 peer-checked:bg-accent-50 rounded-xl px-4 py-3 transition-all duration-200 peer-checked:shadow-soft">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-6 h-6 mb-1 text-primary-600 peer-checked:text-accent-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                            </svg>
                                            <span class="text-sm font-semibold text-primary-900">仕事</span>
                                        </div>
                                    </div>
                                </label>
                                <label class="relative flex items-center justify-center cursor-pointer">
                                    <input type="radio" name="relationship" value="romantic" required class="peer sr-only">
                                    <div class="w-full bg-white border-2 border-primary-200 peer-checked:border-accent-500 peer-checked:bg-accent-50 rounded-xl px-4 py-3 transition-all duration-200 peer-checked:shadow-soft">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-6 h-6 mb-1 text-primary-600 peer-checked:text-accent-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                            </svg>
                                            <span class="text-sm font-semibold text-primary-900">恋人</span>
                                        </div>
                                    </div>
                                </label>
                                <label class="relative flex items-center justify-center cursor-pointer">
                                    <input type="radio" name="relationship" value="family" required class="peer sr-only">
                                    <div class="w-full bg-white border-2 border-primary-200 peer-checked:border-accent-500 peer-checked:bg-accent-50 rounded-xl px-4 py-3 transition-all duration-200 peer-checked:shadow-soft">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-6 h-6 mb-1 text-primary-600 peer-checked:text-accent-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                            </svg>
                                            <span class="text-sm font-semibold text-primary-900">家族</span>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label for="reply_intent" class="block text-sm font-semibold text-primary-900">
                                返信したい内容（日本語でOK）
                            </label>
                            <textarea id="reply_intent" name="reply_intent" required
                                placeholder="例: 元気だよ！最近は仕事が忙しくて、なかなか会えなかったね。今度一緒にご飯でも行こう。"
                                class="w-full border-2 border-primary-200 rounded-xl px-5 py-4 focus:outline-none focus:border-accent-500 focus:ring-4 focus:ring-accent-100 resize-none transition-all duration-200 bg-white/50 backdrop-blur-sm text-primary-900 placeholder-primary-400"
                                rows="5"></textarea>
                        </div>

                        <button type="submit" id="submitBtn"
                            class="w-full bg-gradient-to-r from-primary-800 to-primary-900 hover:from-primary-900 hover:to-primary-800 text-white px-6 py-4 rounded-xl font-semibold shadow-soft hover:shadow-soft-lg transition-all duration-300 transform hover:-translate-y-0.5">
                            返信文を生成する
                        </button>

                        <!-- ローディング表示 -->
                        <div id="loadingIndicator" class="hidden">
                            <div class="bg-gradient-to-br from-accent-50 to-accent-100 border-2 border-accent-300 rounded-xl p-8 text-center shadow-soft">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-white shadow-inner-soft mb-4">
                                    <div class="w-10 h-10 border-4 border-accent-200 border-t-accent-600 rounded-full animate-spin"></div>
                                </div>
                                <p class="text-primary-900 font-semibold text-lg mb-2">返信文を作成中です</p>
                                <p class="text-primary-600">少々お待ちください（10-30秒程度）</p>
                            </div>
                        </div>
                    </form>

                    <div class="mt-10 pt-8 border-t border-primary-200">
                        <h3 class="text-sm font-semibold text-primary-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-accent-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            使い方
                        </h3>
                        <div class="space-y-3">
                            <div class="flex items-start">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-accent-100 text-accent-700 text-sm font-semibold mr-3 flex-shrink-0 mt-0.5">1</span>
                                <p class="text-primary-700">友達からの英語メッセージを入力</p>
                            </div>
                            <div class="flex items-start">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-accent-100 text-accent-700 text-sm font-semibold mr-3 flex-shrink-0 mt-0.5">2</span>
                                <p class="text-primary-700">あなたが返信したい内容を日本語で入力</p>
                            </div>
                            <div class="flex items-start">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-accent-100 text-accent-700 text-sm font-semibold mr-3 flex-shrink-0 mt-0.5">3</span>
                                <p class="text-primary-700">単語帳の単語を活用した自然な英語の返信文を提案</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script>
        function showLoading() {
            // ボタンを無効化
            document.getElementById('submitBtn').disabled = true;
            document.getElementById('submitBtn').classList.add('opacity-50', 'cursor-not-allowed');

            // ローディング表示
            document.getElementById('loadingIndicator').classList.remove('hidden');

            // フォーム送信を続行
            return true;
        }
    </script>
</x-template>
