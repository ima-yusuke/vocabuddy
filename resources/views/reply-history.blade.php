<x-template title="返信履歴">
    <div class="min-h-screen bg-[#ffeb54]">
        <section class="py-16 px-4 sm:px-6 lg:px-8">
            <div class="max-w-6xl mx-auto">
                <div class="mb-10">
                    <a href="{{route('ShowReplyAssistant')}}" class="inline-flex items-center text-sm text-black hover:text-black font-medium transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        返信アシスタントに戻る
                    </a>
                </div>

                <div class="bg-white border-2 border-black rounded-2xl p-10 shadow-soft-lg">
                    <div class="mb-8">
                        <h2 class="text-3xl font-bold text-black mb-4">返信履歴</h2>
                        <p class="text-black">過去に生成した返信の履歴を確認できます</p>
                    </div>

                    <!-- カテゴリフィルター -->
                    <div class="mb-8">
                        <form method="get" action="{{route('ShowReplyHistory')}}" class="flex gap-3">
                            <select name="category" class="border-2 border-black rounded-xl px-4 py-2 focus:outline-none focus:border-[#ffeb54] focus:ring-4 focus:ring-[#ffeb54]/20 bg-white text-black">
                                <option value="">全てのカテゴリ</option>
                                <option value="friend" {{ $category === 'friend' ? 'selected' : '' }}>友達</option>
                                <option value="romantic" {{ $category === 'romantic' ? 'selected' : '' }}>恋人</option>
                                <option value="work" {{ $category === 'work' ? 'selected' : '' }}>仕事</option>
                                <option value="family" {{ $category === 'family' ? 'selected' : '' }}>家族</option>
                            </select>
                            <button type="submit" class="bg-black hover:bg-[#1A1A1A] text-white px-6 py-2 rounded-xl font-semibold transition-colors">
                                フィルター
                            </button>
                            @if($category)
                                <a href="{{route('ShowReplyHistory')}}" class="bg-white hover:bg-gray-100 border-2 border-black text-black px-6 py-2 rounded-xl font-semibold transition-colors">
                                    クリア
                                </a>
                            @endif
                        </form>
                    </div>

                    <!-- 履歴一覧 -->
                    @if($templates->count() > 0)
                        <div class="space-y-6">
                            @foreach($templates as $template)
                                <div class="border-2 border-black rounded-xl p-6 hover:border-[#ffeb54] transition-colors">
                                    <div class="flex items-start justify-between mb-4">
                                        <div class="flex items-center gap-3">
                                            @if($template->category)
                                                <span class="px-3 py-1 bg-[#ffeb54]/30 border border-black rounded-lg text-sm font-semibold text-black">
                                                    {{ ucfirst($template->category) }}
                                                </span>
                                            @endif
                                            <span class="text-sm text-gray-600">
                                                使用回数: <span class="font-semibold text-black">{{ $template->times_used }}</span>回
                                            </span>
                                        </div>
                                        <span class="text-xs text-gray-500">
                                            {{ $template->created_at->format('Y/m/d H:i') }}
                                        </span>
                                    </div>

                                    <div class="space-y-4">
                                        <div class="bg-gray-50 rounded-lg p-4">
                                            <p class="text-xs font-semibold text-gray-600 mb-1">相手のメッセージ</p>
                                            <p class="text-black">{{ $template->partner_message }}</p>
                                        </div>

                                        <div class="bg-gray-50 rounded-lg p-4">
                                            <p class="text-xs font-semibold text-gray-600 mb-1">返信意図</p>
                                            <p class="text-black">{{ $template->intent_ja }}</p>
                                        </div>

                                        <div class="bg-[#ffeb54]/10 rounded-lg p-4 border border-[#ffeb54]">
                                            <p class="text-xs font-semibold text-black mb-2">生成された返信</p>
                                            <p class="text-black font-medium mb-2">{{ $template->reply_en }}</p>
                                            @if($template->reply_ja)
                                                <p class="text-sm text-gray-600">{{ $template->reply_ja }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                            </svg>
                            <p class="text-gray-600">まだ返信履歴がありません</p>
                            <a href="{{route('ShowReplyAssistant')}}" class="inline-block mt-4 bg-black text-white px-6 py-3 rounded-xl font-semibold hover:bg-[#1A1A1A] transition-colors">
                                返信を作成する
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>
</x-template>
