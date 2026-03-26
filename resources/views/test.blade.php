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
                    <div class="text-center mb-8">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex-1">
                                @if(isset($currentQuestionNumber) && isset($totalCount))
                                    <span class="inline-block px-3 py-1 bg-[#ffeb54]/20 text-black text-sm font-semibold rounded-full">
                                        第 {{ $currentQuestionNumber }} / {{ $totalCount }} 問
                                    </span>
                                @endif
                            </div>
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-[#ffeb54]">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="flex-1 text-right">
                                @if(isset($remainingQuestions))
                                    <span class="inline-block px-3 py-1 bg-gray-200 text-black text-sm font-semibold rounded-full">
                                        残り {{ $remainingQuestions }} 問
                                    </span>
                                @endif
                            </div>
                        </div>
                        <h2 class="text-xl font-semibold text-black mb-4">
                            この単語の意味は？
                        </h2>
                    </div>

                    <div class="my-12 text-center">
                        <div class="inline-block">
                            <p class="text-3xl sm:text-4xl md:text-5xl font-bold text-black tracking-wide">{{$correctWord->word}}</p>
                            <div class="h-1 bg-[#ffeb54] mt-4 rounded-full"></div>
                        </div>
                    </div>

                    @if($correctWord->en_example)
                        <div class="mb-10 bg-[#ffeb54]/10 rounded-xl p-6 border border-black">
                            <div class="flex items-center mb-3">
                                <svg class="w-5 h-5 mr-2 text-[#ffeb54]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                </svg>
                                <span class="text-sm font-semibold text-black">例文</span>
                            </div>
                            <p class="text-black italic leading-relaxed">{{$correctWord->en_example}}</p>
                        </div>
                    @endif

                    <form method="post" action="{{route('CheckAnswer')}}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="correct_answer" value="{{$correctMeaning}}">
                        <input type="hidden" name="word_id" value="{{$correctWord->id}}">

                        @foreach($options as $index => $option)
                            <button type="submit" name="answer" value="{{$option}}"
                                class="group w-full text-left bg-white hover:bg-[#ffeb54]/10 border-2 border-black hover:border-[#ffeb54] rounded-xl p-5 transition-all duration-300 shadow-soft hover:shadow-soft-lg transform hover:-translate-y-0.5">
                                <div class="flex items-center">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-200 group-hover:bg-[#ffeb54]/30 text-black group-hover:text-black font-semibold mr-4 transition-colors flex-shrink-0">
                                        {{ chr(65 + $index) }}
                                    </span>
                                    <span class="text-black font-medium">{{$option}}</span>
                                </div>
                            </button>
                        @endforeach
                    </form>
                </div>
            </div>
        </section>
    </div>
</x-template>
