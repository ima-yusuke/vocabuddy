<x-template title="メモを作成">
    <div class="min-h-screen bg-[#ffeb54]">
        <section class="py-16 px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto">
                <a href="{{ route('notes.index') }}" class="inline-block mb-6 text-black font-semibold hover:underline">← 一覧に戻る</a>
                <div class="bg-white border-2 border-black rounded-2xl p-10">
                    <h1 class="text-2xl font-bold text-black mb-8">新しいメモ</h1>
                    <form method="post" action="{{ route('notes.store') }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        @include('notes._form')
                        <button type="submit"
                            class="w-full bg-black text-[#ffeb54] px-6 py-3 rounded-xl font-semibold hover:bg-gray-800 transition-all duration-200">
                            保存する
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </div>
</x-template>
