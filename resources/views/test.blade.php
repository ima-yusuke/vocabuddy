<x-template title="単語テスト">
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

                <div class="bg-white/80 backdrop-blur-sm border border-primary-100 rounded-2xl p-10 shadow-soft-lg">
                    <div class="text-center mb-8">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex-1">
                                @if(isset($currentQuestionNumber) && isset($totalCount))
                                    <span class="inline-block px-3 py-1 bg-accent-100 text-accent-800 text-sm font-semibold rounded-full">
                                        第 {{ $currentQuestionNumber }} / {{ $totalCount }} 問
                                    </span>
                                @endif
                            </div>
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-br from-accent-400 to-accent-600">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="flex-1 text-right">
                                @if(isset($remainingQuestions))
                                    <span class="inline-block px-3 py-1 bg-primary-100 text-primary-800 text-sm font-semibold rounded-full">
                                        残り {{ $remainingQuestions }} 問
                                    </span>
                                @endif
                            </div>
                        </div>
                        <h2 class="text-xl font-semibold text-primary-700 mb-4">
                            この単語の意味は？
                        </h2>
                    </div>

                    <div class="my-12 text-center">
                        <div class="inline-block">
                            <p class="text-3xl sm:text-4xl md:text-5xl font-bold text-primary-900 tracking-wide">{{$correctWord->word}}</p>
                            <div class="h-1 bg-gradient-to-r from-transparent via-accent-500 to-transparent mt-4 rounded-full"></div>
                        </div>
                    </div>

                    @if($correctWord->en_example)
                        <div class="mb-10 bg-gradient-to-br from-primary-50 to-accent-50/30 rounded-xl p-6 border border-primary-100">
                            <div class="flex items-center mb-3">
                                <svg class="w-5 h-5 mr-2 text-accent-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                </svg>
                                <span class="text-sm font-semibold text-primary-700">例文</span>
                            </div>
                            <p class="text-primary-800 italic leading-relaxed">{{$correctWord->en_example}}</p>
                        </div>
                    @endif

                    <form method="post" action="{{route('CheckAnswer')}}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="correct_answer" value="{{$correctMeaning}}">
                        <input type="hidden" name="word_id" value="{{$correctWord->id}}">

                        @foreach($options as $index => $option)
                            <button type="submit" name="answer" value="{{$option}}"
                                class="group w-full text-left bg-white hover:bg-gradient-to-r hover:from-accent-50 hover:to-accent-100 border-2 border-primary-200 hover:border-accent-400 rounded-xl p-5 transition-all duration-300 shadow-soft hover:shadow-soft-lg transform hover:-translate-y-0.5">
                                <div class="flex items-center">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-primary-100 group-hover:bg-accent-200 text-primary-700 group-hover:text-accent-800 font-semibold mr-4 transition-colors flex-shrink-0">
                                        {{ chr(65 + $index) }}
                                    </span>
                                    <span class="text-primary-900 font-medium">{{$option}}</span>
                                </div>
                            </button>
                        @endforeach
                    </form>
                </div>
            </div>
        </section>
    </div>
</x-template>
