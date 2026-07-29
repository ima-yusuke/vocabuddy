<x-template :title="$note->title">
    <div class="min-h-screen bg-[#ffeb54]">
        <section class="py-16 px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto">
                <a href="{{ route('notes.index') }}" class="inline-block mb-6 text-black font-semibold hover:underline">← 一覧に戻る</a>

                @if(session('success'))
                    <div class="bg-white border-2 border-black rounded-xl p-4 mb-6 font-semibold text-black">
                        {{ session('success') }}
                    </div>
                @endif

                <article class="bg-white border-2 border-black rounded-2xl p-10">
                    <div class="flex items-start justify-between mb-6">
                        <div>
                            <h1 class="text-3xl font-bold text-black mb-2">{{ $note->title }}</h1>
                            <div class="flex items-center gap-3">
                                @if($note->category)
                                    <span class="px-3 py-1 rounded-full bg-[#ffeb54] border border-black text-sm font-semibold text-black">
                                        {{ $note->category->name }}
                                    </span>
                                @endif
                                <span class="text-gray-400 text-sm">{{ $note->updated_at->format('Y/m/d') }} 更新</span>
                            </div>
                        </div>
                        <div class="shrink-0 flex items-center gap-2">
                            <a href="{{ route('notes.edit', $note->id) }}"
                                class="bg-black text-[#ffeb54] px-5 py-2 rounded-xl font-semibold hover:bg-gray-800 transition-all duration-200">
                                編集
                            </a>
                            <form method="post" action="{{ route('notes.destroy', $note->id) }}"
                                onsubmit="return confirm('このメモを削除しますか？画像も一緒に削除されます。');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="bg-white text-black border-2 border-black px-5 py-2 rounded-xl font-semibold hover:bg-black hover:text-[#ffeb54] transition-all duration-200">
                                    削除
                                </button>
                            </form>
                        </div>
                    </div>

                    @if($note->body)
                        <div class="text-black leading-relaxed whitespace-pre-wrap mb-8">{{ $note->body }}</div>
                    @endif

                    @if($note->images->isNotEmpty())
                        <div class="space-y-6">
                            @foreach($note->images as $image)
                                <img src="{{ asset('storage/' . $image->path) }}" alt=""
                                    class="w-full border-2 border-black rounded-2xl">
                            @endforeach
                        </div>
                    @endif
                </article>
            </div>
        </section>
    </div>
</x-template>
