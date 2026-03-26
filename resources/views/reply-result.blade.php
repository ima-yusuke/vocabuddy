<x-template title="返信文の提案">
    <div class="min-h-screen bg-[#ffeb54]">
        <section class="py-16 px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto">
                <div class="mb-10">
                    <a href="{{route('ShowReplyAssistant')}}" class="inline-flex items-center text-sm text-black hover:text-black font-medium transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        返信アシスタントに戻る
                    </a>
                </div>

                <div class="space-y-6">
                    <!-- 友達のメッセージ -->
                    <div class="bg-white border-2 border-black rounded-2xl p-8 shadow-soft">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <h3 class="text-sm font-semibold text-black mb-3">友達からのメッセージ</h3>
                                <p class="text-black leading-relaxed">{{$friendMessage}}</p>
                            </div>
                        </div>
                    </div>

                    <!-- あなたの返信意図 -->
                    <div class="bg-white border-2 border-black rounded-2xl p-8 shadow-soft">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 rounded-full bg-[#ffeb54]/30 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <h3 class="text-sm font-semibold text-black mb-3">あなたの返信意図</h3>
                                <p class="text-black leading-relaxed">{{$replyIntent}}</p>
                            </div>
                        </div>
                    </div>

                    <!-- 生成された返信文 -->
                    <div class="bg-white backdrop-blur-sm border-2 border-[#ffeb54] rounded-2xl p-6 sm:p-8 shadow-soft-lg">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                            <h3 class="text-lg sm:text-xl font-bold text-black flex items-center">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 mr-2 text-accent-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                提案された返信文
                            </h3>
                            <button onclick="copyEnglishOnly()" id="copyButton" class="w-full sm:w-auto bg-gradient-to-r from-primary-800 to-primary-900 hover:from-primary-900 hover:to-primary-800 text-white text-sm px-5 py-2.5 rounded-lg font-semibold shadow-soft hover:shadow-soft-lg transition-all duration-300 transform hover:-translate-y-0.5 flex items-center justify-center whitespace-nowrap">
                                <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                                英文をコピー
                            </button>
                        </div>

                        <!-- 英文のみ -->
                        <div class="bg-white/90 backdrop-blur-sm border border-[#ffeb54] rounded-xl p-6 mb-6 shadow-inner-soft">
                            <div class="flex items-start mb-3">
                                <span class="inline-block px-3 py-1 bg-accent-100 text-accent-800 text-xs font-semibold rounded-full">英語返信</span>
                            </div>
                            <p id="english-reply" class="text-lg text-black leading-relaxed font-medium">{{$englishReply ?? ''}}</p>
                        </div>

                        <!-- 日本語訳と解説 -->
                        <div class="border-t-2 border-primary-100 pt-6">
                            <div class="flex items-start mb-3">
                                <span class="inline-block px-3 py-1 bg-primary-100 text-primary-800 text-xs font-semibold rounded-full">解説</span>
                            </div>
                            <div class="prose max-w-none">
                                <div class="whitespace-pre-wrap text-primary-800 leading-relaxed">{{$generatedText}}</div>
                            </div>
                        </div>
                    </div>

                    <!-- 使用された単語 -->
                    @if(count($usedWords) > 0)
                        <div class="bg-white border-2 border-black rounded-2xl p-8 shadow-soft">
                            <h3 class="text-sm font-semibold text-black mb-6 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-accent-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                使用された単語帳の単語
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($usedWords as $word)
                                    <div class="bg-gradient-to-br from-white to-primary-50 border border-primary-200 rounded-xl p-5 hover:shadow-soft transition-all duration-300">
                                        <p class="font-bold text-black text-lg mb-2">{{$word->word}}</p>
                                        <div class="space-y-1">
                                            @foreach($word->japanese as $ja_word)
                                                <p class="text-sm text-black">・{{$ja_word->japanese}}</p>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- アクションボタン -->
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="{{route('ShowReplyAssistant')}}" class="flex-1 text-center bg-gradient-to-r from-primary-800 to-primary-900 hover:from-primary-900 hover:to-primary-800 text-white px-6 py-4 rounded-xl font-semibold shadow-soft hover:shadow-soft-lg transition-all duration-300 transform hover:-translate-y-0.5">
                            別の返信を作成
                        </a>
                        <a href="{{route('words.index')}}" class="flex-1 text-center bg-white hover:bg-primary-50 border-2 border-primary-200 text-black px-6 py-4 rounded-xl font-semibold shadow-soft hover:shadow-soft-lg transition-all duration-300">
                            単語帳に戻る
                        </a>
                    </div>

                </div>
            </div>
        </section>
    </div>

    <script>
        function copyEnglishOnly() {
            const text = document.getElementById('english-reply').textContent;
            const button = document.getElementById('copyButton');

            navigator.clipboard.writeText(text).then(() => {
                // ボタンの表示を一時的に変更
                const originalHTML = button.innerHTML;
                button.innerHTML = `
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    コピーしました
                `;
                button.classList.add('from-green-600', 'to-green-700');
                button.classList.remove('from-primary-800', 'to-primary-900');

                setTimeout(() => {
                    button.innerHTML = originalHTML;
                    button.classList.remove('from-green-600', 'to-green-700');
                    button.classList.add('from-primary-800', 'to-primary-900');
                }, 2000);
            }).catch(err => {
                console.error('コピーに失敗しました:', err);
                alert('コピーに失敗しました。もう一度お試しください。');
            });
        }
    </script>
</x-template>
