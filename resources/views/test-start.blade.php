<x-template title="単語テスト">
    <div class="min-h-screen bg-[#ffeb54]">
        <section class="py-16 px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto">
                <div class="mb-10">
                    <a href="{{route('words.index')}}" class="inline-flex items-center text-sm text-black hover:text-black font-medium transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        単語帳に戻る
                    </a>
                </div>

                <div class="bg-white border-2 border-black rounded-2xl p-10 shadow-soft-lg">
                    <div class="text-center mb-10">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-[#ffeb54] mb-6">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h2 class="text-3xl font-bold text-black mb-3">
                            単語テスト
                        </h2>
                        <p class="text-black">
                            問題数を選択してテストを開始してください
                        </p>
                    </div>

                    <form method="GET" action="{{route('StartTest')}}" class="space-y-8">
                        <div class="space-y-4">
                            <label class="block text-sm font-semibold text-black text-center mb-6">
                                問題数を選択 <span class="text-red-600">*</span>
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <label class="relative flex items-center justify-center cursor-pointer">
                                    <input type="radio" name="count" value="10" required class="peer sr-only" checked>
                                    <div class="w-full bg-white border-2 border-black peer-checked:border-[#ffeb54] peer-checked:bg-[#ffeb54]/10 rounded-xl px-6 py-8 transition-all duration-200 peer-checked:shadow-soft-lg hover:border-[#ffeb54]">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-12 h-12 mb-3 text-black peer-checked:text-[#ffeb54]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                            </svg>
                                            <span class="text-3xl font-bold text-black mb-1">10</span>
                                            <span class="text-sm text-black">問</span>
                                            <span class="text-xs text-gray-600 mt-2">約5分</span>
                                        </div>
                                    </div>
                                </label>
                                <label class="relative flex items-center justify-center cursor-pointer">
                                    <input type="radio" name="count" value="20" required class="peer sr-only">
                                    <div class="w-full bg-white border-2 border-black peer-checked:border-[#ffeb54] peer-checked:bg-[#ffeb54]/10 rounded-xl px-6 py-8 transition-all duration-200 peer-checked:shadow-soft-lg hover:border-[#ffeb54]">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-12 h-12 mb-3 text-black peer-checked:text-[#ffeb54]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                                            </svg>
                                            <span class="text-3xl font-bold text-black mb-1">20</span>
                                            <span class="text-sm text-black">問</span>
                                            <span class="text-xs text-gray-600 mt-2">約10分</span>
                                        </div>
                                    </div>
                                </label>
                                <label class="relative flex items-center justify-center cursor-pointer">
                                    <input type="radio" name="count" value="30" required class="peer sr-only">
                                    <div class="w-full bg-white border-2 border-black peer-checked:border-[#ffeb54] peer-checked:bg-[#ffeb54]/10 rounded-xl px-6 py-8 transition-all duration-200 peer-checked:shadow-soft-lg hover:border-[#ffeb54]">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-12 h-12 mb-3 text-black peer-checked:text-[#ffeb54]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                                            </svg>
                                            <span class="text-3xl font-bold text-black mb-1">30</span>
                                            <span class="text-sm text-black">問</span>
                                            <span class="text-xs text-gray-600 mt-2">約15分</span>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <button type="submit" id="startButton"
                            class="w-full bg-black hover:bg-[#1A1A1A] text-white px-6 py-4 rounded-xl font-semibold shadow-soft hover:shadow-soft-lg transition-all duration-300 transform hover:-translate-y-0.5">
                            テストを開始
                        </button>

                        <!-- ローディング表示 -->
                        <div id="loadingIndicator" class="hidden">
                            <div class="bg-[#ffeb54]/20 border-2 border-black rounded-xl p-8 text-center shadow-soft">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-white shadow-inner-soft mb-4">
                                    <div class="w-10 h-10 border-4 border-[#ffeb54] border-t-black rounded-full animate-spin"></div>
                                </div>
                                <p class="text-black font-semibold text-lg mb-2">問題を生成中です</p>
                                <p class="text-black">少々お待ちください（10-20秒程度）</p>
                            </div>
                        </div>
                    </form>

                    <div class="mt-10 pt-8 border-t border-black">
                        <h3 class="text-sm font-semibold text-black mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-[#ffeb54]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            テストについて
                        </h3>
                        <ul class="space-y-2 text-sm text-black">
                            <li class="flex items-start">
                                <span class="inline-block w-1.5 h-1.5 rounded-full bg-[#ffeb54] mt-2 mr-3 flex-shrink-0"></span>
                                選択した問題数分、似た意味の紛らわしい選択肢で出題されます
                            </li>
                            <li class="flex items-start">
                                <span class="inline-block w-1.5 h-1.5 rounded-full bg-[#ffeb54] mt-2 mr-3 flex-shrink-0"></span>
                                問題生成に最初だけ10-20秒程度かかります
                            </li>
                            <li class="flex items-start">
                                <span class="inline-block w-1.5 h-1.5 rounded-full bg-[#ffeb54] mt-2 mr-3 flex-shrink-0"></span>
                                一度生成されれば、問題はサクサク進みます
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script>
        document.querySelector('form').addEventListener('submit', function() {
            // ボタンを無効化
            document.getElementById('startButton').disabled = true;
            document.getElementById('startButton').classList.add('opacity-50', 'cursor-not-allowed');

            // ローディング表示
            document.getElementById('loadingIndicator').classList.remove('hidden');

            // フォーム送信を続行
            return true;
        });
    </script>
</x-template>
