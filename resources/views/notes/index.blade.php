<x-template title="学習ノート">
    <div class="min-h-screen bg-[#ffeb54]">
        <section class="py-16 px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto">
                {{-- ヘッダー --}}
                <div class="flex items-center justify-between mb-10">
                    <h1 class="text-3xl font-bold text-black">学習ノート</h1>
                    <a href="{{ route('notes.create') }}"
                        class="bg-black text-[#ffeb54] px-6 py-3 rounded-xl font-semibold hover:bg-gray-800 transition-all duration-200">
                        ＋ 新しいメモ
                    </a>
                </div>

                {{-- フラッシュメッセージ --}}
                @if(session('success'))
                    <div class="bg-white border-2 border-black rounded-xl p-4 mb-6 font-semibold text-black">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- カテゴリ絞り込み --}}
                @if($categories->isNotEmpty())
                    <div class="flex flex-wrap gap-2 mb-8">
                        <a href="{{ route('notes.index') }}"
                            class="px-4 py-2 rounded-full border-2 border-black font-semibold transition-all duration-200 {{ !$categoryId ? 'bg-black text-[#ffeb54]' : 'bg-white text-black hover:bg-black hover:text-[#ffeb54]' }}">
                            すべて
                        </a>
                        @foreach($categories as $category)
                            <a href="{{ route('notes.index', ['category' => $category->id]) }}"
                                class="px-4 py-2 rounded-full border-2 border-black font-semibold transition-all duration-200 {{ (string)$categoryId === (string)$category->id ? 'bg-black text-[#ffeb54]' : 'bg-white text-black hover:bg-black hover:text-[#ffeb54]' }}">
                                {{ $category->name }}
                            </a>
                        @endforeach
                    </div>
                @endif

                {{-- メモ一覧 --}}
                @if($notes->isEmpty())
                    <div class="bg-white border-2 border-black rounded-2xl p-10 text-center">
                        <p class="text-black font-semibold mb-2">まだメモがありません</p>
                        <p class="text-gray-600 text-sm">「＋ 新しいメモ」から学んだことを記録しましょう</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($notes as $note)
                            <a href="{{ route('notes.show', $note->id) }}"
                                class="block bg-white border-2 border-black rounded-2xl p-6 hover:shadow-soft-lg hover:-translate-y-0.5 transition-all duration-200">
                                <div class="flex items-center justify-between mb-2">
                                    <h2 class="text-xl font-bold text-black">{{ $note->title }}</h2>
                                    @if($note->category)
                                        <span class="shrink-0 ml-4 px-3 py-1 rounded-full bg-[#ffeb54] border border-black text-sm font-semibold text-black">
                                            {{ $note->category->name }}
                                        </span>
                                    @endif
                                </div>
                                @if($note->body)
                                    <p class="text-gray-600 text-sm line-clamp-2">{{ $note->body }}</p>
                                @endif
                                <p class="text-gray-400 text-xs mt-3">{{ $note->updated_at->format('Y/m/d') }} 更新</p>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    </div>
</x-template>
