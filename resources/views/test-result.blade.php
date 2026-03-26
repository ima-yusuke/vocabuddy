<x-template title="テスト結果">
    <div class="min-h-screen bg-gradient-to-br from-primary-50 via-white to-accent-50">
        <section class="py-16 px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto">
                <div class="bg-white/80 backdrop-blur-sm border border-primary-100 rounded-2xl p-10 shadow-soft-lg text-center">
                    @if($isCorrect)
                        <div class="mb-10">
                            <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-green-400 to-green-600 rounded-full mb-6 shadow-soft-lg animate-bounce">
                                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <h2 class="text-4xl font-bold text-green-600 mb-3">正解！</h2>
                            <p class="text-primary-600">素晴らしい！</p>
                        </div>
                    @else
                        <div class="mb-10">
                            <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-red-400 to-red-600 rounded-full mb-6 shadow-soft-lg">
                                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </div>
                            <h2 class="text-4xl font-bold text-red-600 mb-3">不正解</h2>
                            <p class="text-primary-600">もう一度確認してみましょう</p>
                        </div>
                    @endif

                    <div class="mb-10 pb-10 border-b-2 border-primary-100">
                        <div class="inline-block mb-6">
                            <p class="text-3xl font-bold text-primary-900 tracking-wide">{{$word->word}}</p>
                            <div class="h-1 bg-gradient-to-r from-transparent via-accent-500 to-transparent mt-3 rounded-full"></div>
                        </div>

                        <div class="max-w-md mx-auto space-y-6">
                            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border-2 border-green-200">
                                <p class="text-sm font-semibold text-green-800 mb-2 flex items-center justify-center">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    正解
                                </p>
                                <p class="text-xl font-bold text-green-700">{{$correctAnswer}}</p>
                            </div>

                            @if(!$isCorrect)
                                <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-6 border-2 border-red-200">
                                    <p class="text-sm font-semibold text-red-800 mb-2 flex items-center justify-center">
                                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        あなたの回答
                                    </p>
                                    <p class="text-xl font-bold text-red-700">{{$selectedAnswer}}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mb-10 text-left max-w-2xl mx-auto">
                        <div class="bg-gradient-to-br from-primary-50 to-accent-50/30 rounded-xl p-8 border border-primary-200">
                            <h3 class="text-sm font-semibold text-primary-900 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-accent-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                                この単語の意味
                            </h3>
                            <div class="space-y-2 mb-6">
                                @foreach($word->japanese as $ja_word)
                                    <div class="flex items-start">
                                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-accent-500 mt-2 mr-3 flex-shrink-0"></span>
                                        <p class="text-primary-800 font-medium">{{$ja_word->japanese}}</p>
                                    </div>
                                @endforeach
                            </div>

                            @if($word->en_example || $word->jp_example)
                                <div class="pt-6 border-t border-primary-200">
                                    <div class="flex items-center mb-3">
                                        <svg class="w-4 h-4 mr-2 text-accent-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                        </svg>
                                        <span class="text-xs font-semibold text-primary-700">例文</span>
                                    </div>
                                    <div class="space-y-2">
                                        @if($word->en_example)
                                            <p class="text-primary-700 italic leading-relaxed">{{$word->en_example}}</p>
                                        @endif
                                        @if($word->jp_example)
                                            <p class="text-primary-600 leading-relaxed">{{$word->jp_example}}</p>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        @if(isset($hasMoreQuestions) && $hasMoreQuestions)
                            <a href="{{route('ShowQuestion')}}" class="bg-gradient-to-r from-primary-800 to-primary-900 hover:from-primary-900 hover:to-primary-800 text-white px-8 py-4 rounded-xl font-semibold shadow-soft hover:shadow-soft-lg transition-all duration-300 transform hover:-translate-y-0.5">
                                次の問題へ
                            </a>
                        @else
                            <a href="{{route('ShowTest')}}" class="bg-gradient-to-r from-primary-800 to-primary-900 hover:from-primary-900 hover:to-primary-800 text-white px-8 py-4 rounded-xl font-semibold shadow-soft hover:shadow-soft-lg transition-all duration-300 transform hover:-translate-y-0.5">
                                新しいテストを開始
                            </a>
                        @endif
                        <a href="{{route('words.index')}}" class="bg-white hover:bg-primary-50 border-2 border-primary-200 text-primary-900 px-8 py-4 rounded-xl font-semibold shadow-soft hover:shadow-soft-lg transition-all duration-300">
                            単語帳に戻る
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-template>
