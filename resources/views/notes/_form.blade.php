{{-- エラーメッセージ --}}
@if($errors->any())
    <div class="bg-white border-2 border-black rounded-xl p-4 mb-6">
        <h3 class="text-black font-bold mb-2">入力エラー</h3>
        <ul class="list-disc list-inside text-black text-sm">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div>
    <label for="title" class="block text-sm font-semibold text-black mb-2">タイトル <span class="text-black">*</span></label>
    <input type="text" id="title" name="title" value="{{ old('title', $note->title ?? '') }}"
        placeholder="例: 現在分詞とは？"
        class="w-full border-2 border-black rounded-xl px-5 py-3 focus:outline-none focus:ring-4 focus:ring-[#ffeb54]/40 bg-white text-black placeholder-gray-400">
</div>

<div>
    <label for="note_category_id" class="block text-sm font-semibold text-black mb-2">カテゴリ</label>
    <select id="note_category_id" name="note_category_id"
        class="w-full border-2 border-black rounded-xl px-5 py-3 focus:outline-none focus:ring-4 focus:ring-[#ffeb54]/40 bg-white text-black">
        <option value="">カテゴリなし</option>
        @foreach($categories as $category)
            <option value="{{ $category->id }}"
                @selected((string) old('note_category_id', $note->note_category_id ?? '') === (string) $category->id)>
                {{ $category->name }}
            </option>
        @endforeach
    </select>
    <input type="text" name="new_category" value="{{ old('new_category') }}"
        placeholder="または新しいカテゴリ名を入力（例: 文法）"
        class="mt-2 w-full border-2 border-black rounded-xl px-5 py-3 focus:outline-none focus:ring-4 focus:ring-[#ffeb54]/40 bg-white text-black placeholder-gray-400">
    <p class="text-xs text-gray-600 mt-1">新しいカテゴリ名を入力した場合はそちらが優先されます</p>
</div>

<div>
    <label for="body" class="block text-sm font-semibold text-black mb-2">本文</label>
    <textarea id="body" name="body" rows="10"
        placeholder="学んだことを自由にメモしましょう"
        class="w-full border-2 border-black rounded-xl px-5 py-3 focus:outline-none focus:ring-4 focus:ring-[#ffeb54]/40 bg-white text-black placeholder-gray-400 resize-y">{{ old('body', $note->body ?? '') }}</textarea>
</div>

{{-- 既存画像（編集時のみ） --}}
@if(isset($note) && $note->images->isNotEmpty())
    <div>
        <p class="block text-sm font-semibold text-black mb-2">アップロード済みの画像（チェックで削除）</p>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
            @foreach($note->images as $image)
                <label class="block cursor-pointer">
                    <img src="{{ asset('storage/' . $image->path) }}" alt=""
                        class="w-full h-32 object-cover border-2 border-black rounded-xl">
                    <span class="flex items-center mt-1 text-sm text-black">
                        <input type="checkbox" name="delete_image_ids[]" value="{{ $image->id }}"
                            class="mr-2 border-2 border-black rounded">
                        削除する
                    </span>
                </label>
            @endforeach
        </div>
    </div>
@endif

<div>
    <label for="images" class="block text-sm font-semibold text-black mb-2">画像を追加（複数選択可・最大10枚・1枚5MBまで）</label>
    <input type="file" id="images" name="images[]" multiple accept=".jpg,.jpeg,.png,.webp,.gif"
        class="w-full border-2 border-black rounded-xl px-5 py-3 bg-white text-black file:mr-4 file:py-1 file:px-4 file:rounded-full file:border-2 file:border-black file:bg-[#ffeb54] file:text-black file:font-semibold">
</div>
