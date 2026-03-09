<x-template title="単語帳">
    <div class="min-h-screen bg-gradient-to-br from-primary-50 via-white to-accent-50">
        <section class="py-16 px-4 sm:px-6 lg:px-8">
            {{--新規登録(権限のあるユーザーでログインした場合のみ）--}}
            @hasanyrole('membership')
                <div class="max-w-4xl mx-auto mb-16">
                    <div class="bg-white/80 backdrop-blur-sm border border-primary-100 rounded-2xl p-10 shadow-soft-lg">
                        <div class="flex items-center mb-8">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-accent-400 to-accent-600 flex items-center justify-center mr-4">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                            </div>
                            <h2 class="text-2xl font-bold text-primary-900">
                                新しい単語を追加
                            </h2>
                        </div>

                        <form method="post" action="{{route('AddWord')}}" class="space-y-6" id="add_form">
                            @csrf
                            <div>
                                <label for="word" class="block text-sm font-semibold text-primary-900 mb-2">
                                    英単語
                                </label>
                                <input type="text" id="word" name="word" placeholder="例: serendipity"
                                    class="w-full border-2 border-primary-200 rounded-xl px-5 py-3 focus:outline-none focus:border-accent-500 focus:ring-4 focus:ring-accent-100 transition-all duration-200 bg-white/50 backdrop-blur-sm text-primary-900 placeholder-primary-400">
                            </div>

                            <div>
                                <label for="context" class="block text-sm font-semibold text-primary-900 mb-2">
                                    文脈・例文（任意）
                                </label>
                                <textarea id="context" placeholder="例: I found this word in a mystery novel."
                                    class="w-full border-2 border-primary-200 rounded-xl px-5 py-3 focus:outline-none focus:border-accent-500 focus:ring-4 focus:ring-accent-100 resize-none transition-all duration-200 bg-white/50 backdrop-blur-sm text-primary-900 placeholder-primary-400" rows="2"></textarea>
                            </div>

                            <button type="button" id="autocomplete_btn"
                                class="w-full bg-gradient-to-r from-accent-600 to-accent-700 hover:from-accent-700 hover:to-accent-800 text-white px-6 py-3 rounded-xl font-semibold shadow-soft hover:shadow-soft-lg transition-all duration-300 transform hover:-translate-y-0.5">
                                AI で意味を補完
                            </button>

                            <div id="loading_message" style="display: none;"
                                class="bg-accent-50 border border-accent-200 rounded-xl p-4 text-accent-700 text-sm">
                                AI が単語情報を取得しています...
                            </div>

                            <div id="preview_area" style="display: none;"
                                class="bg-gradient-to-br from-primary-50 to-accent-50/30 rounded-xl p-6 border border-primary-200 space-y-4">
                                <h3 class="text-lg font-bold text-primary-900 mb-4">AI 補完結果</h3>

                                <div>
                                    <label class="block text-sm font-semibold text-primary-900 mb-2">品詞</label>
                                    <input type="text" id="part_of_speech" readonly
                                        class="w-full border-2 border-primary-200 rounded-xl px-5 py-3 bg-white/70 text-primary-900">
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-primary-900 mb-2">発音記号</label>
                                    <input type="text" id="pronunciation" readonly
                                        class="w-full border-2 border-primary-200 rounded-xl px-5 py-3 bg-white/70 text-primary-900">
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-primary-900 mb-2">発音（カタカナ）</label>
                                    <input type="text" id="pronunciation_katakana" readonly
                                        class="w-full border-2 border-primary-200 rounded-xl px-5 py-3 bg-white/70 text-primary-900">
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-primary-900 mb-2">意味</label>
                                    <div id="meanings_container" class="space-y-2">
                                    </div>
                                </div>
                            </div>

                            <div id="manual_meanings">
                                <div>
                                    <label for="jp_word_1" class="block text-sm font-semibold text-primary-900 mb-2">
                                        意味 1
                                    </label>
                                    <input type="text" id="jp_word_1" name="meaningArray[]" placeholder="例: 偶然の幸運な発見"
                                        class="w-full border-2 border-primary-200 rounded-xl px-5 py-3 focus:outline-none focus:border-accent-500 focus:ring-4 focus:ring-accent-100 transition-all duration-200 bg-white/50 backdrop-blur-sm text-primary-900 placeholder-primary-400">
                                </div>

                                <button type="button" id="add_meaning"
                                    class="inline-flex items-center text-sm text-accent-700 hover:text-accent-800 font-semibold transition-colors">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    意味を追加
                                </button>
                            </div>

                            <div>
                                <label for="en_example" class="block text-sm font-semibold text-primary-900 mb-2">
                                    例文（英語）
                                </label>
                                <textarea id="en_example" name="en_example" placeholder="例: It was pure serendipity that we met at the cafe."
                                    class="w-full border-2 border-primary-200 rounded-xl px-5 py-3 focus:outline-none focus:border-accent-500 focus:ring-4 focus:ring-accent-100 resize-none transition-all duration-200 bg-white/50 backdrop-blur-sm text-primary-900 placeholder-primary-400" rows="3"></textarea>
                            </div>

                            <div>
                                <label for="jp_example" class="block text-sm font-semibold text-primary-900 mb-2">
                                    例文（日本語）
                                </label>
                                <textarea id="jp_example" name="jp_example" placeholder="例: カフェで会ったのは純粋な偶然だった。"
                                    class="w-full border-2 border-primary-200 rounded-xl px-5 py-3 focus:outline-none focus:border-accent-500 focus:ring-4 focus:ring-accent-100 resize-none transition-all duration-200 bg-white/50 backdrop-blur-sm text-primary-900 placeholder-primary-400" rows="3"></textarea>
                            </div>

                            <button type="submit"
                                class="w-full bg-gradient-to-r from-primary-800 to-primary-900 hover:from-primary-900 hover:to-primary-800 text-white px-6 py-4 rounded-xl font-semibold shadow-soft hover:shadow-soft-lg transition-all duration-300 transform hover:-translate-y-0.5">
                                登録する
                            </button>
                        </form>
                    </div>
                </div>
            @endhasanyrole

            {{--単語一覧--}}
            <div class="max-w-4xl mx-auto">
                <div class="mb-10">
                    <!-- タイトルとボタン -->
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-6 mb-8">
                        <h2 class="text-2xl font-bold text-primary-900 flex items-center">
                            <svg class="w-7 h-7 mr-3 text-accent-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                            単語一覧
                        </h2>
                        <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                            <a href="{{route('ShowReplyAssistant')}}" class="text-center bg-white hover:bg-primary-50 border-2 border-primary-200 text-primary-900 px-6 py-3 rounded-xl font-semibold shadow-soft hover:shadow-soft-lg transition-all duration-300 text-sm sm:text-base whitespace-nowrap">
                                返信アシスタント
                            </a>
                            <a href="{{route('ShowTest')}}" class="text-center bg-gradient-to-r from-primary-800 to-primary-900 hover:from-primary-900 hover:to-primary-800 text-white px-6 py-3 rounded-xl font-semibold shadow-soft hover:shadow-soft-lg transition-all duration-300 transform hover:-translate-y-0.5 text-sm sm:text-base whitespace-nowrap">
                                単語テスト
                            </a>
                        </div>
                    </div>

                    <!-- 検索フォーム -->
                    <form method="get" action="{{route('ShowIndex')}}" class="mb-6">
                        <div class="flex flex-col sm:flex-row gap-3">
                            <div class="flex-1 relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                <input type="text" name="search" value="{{request('search')}}" placeholder="単語または意味を検索..."
                                    class="w-full pl-11 pr-4 py-3 border-2 border-primary-200 rounded-xl focus:outline-none focus:border-accent-500 focus:ring-4 focus:ring-accent-100 transition-all duration-200 bg-white/50 backdrop-blur-sm text-primary-900 placeholder-primary-400 text-sm sm:text-base">
                            </div>
                            <div class="flex gap-3">
                                <button type="submit" class="flex-1 sm:flex-none bg-gradient-to-r from-primary-800 to-primary-900 hover:from-primary-900 hover:to-primary-800 text-white px-8 py-3 rounded-xl font-semibold shadow-soft hover:shadow-soft-lg transition-all duration-300 text-sm sm:text-base whitespace-nowrap">
                                    検索
                                </button>
                                @if(request('search'))
                                    <a href="{{route('ShowIndex')}}" class="flex-1 sm:flex-none text-center bg-white hover:bg-primary-50 border-2 border-primary-200 text-primary-700 px-6 py-3 rounded-xl font-semibold shadow-soft hover:shadow-soft-lg transition-all duration-300 text-sm sm:text-base whitespace-nowrap">
                                        クリア
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                    <div class="flex items-center text-sm text-primary-600">
                        <svg class="w-4 h-4 mr-2 text-accent-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        @if(request('search'))
                            検索結果: <span class="font-semibold text-primary-900 mx-1">{{count($words)}}</span>件 / 総単語数: <span class="font-semibold text-primary-900 mx-1">{{$totalCount}}</span>件
                        @else
                            総単語数: <span class="font-semibold text-primary-900 mx-1">{{count($words)}}</span>件
                        @endif
                    </div>
                </div>

                <div class="space-y-5">
                    @foreach($words as $word)
                        <div class="bg-white/80 backdrop-blur-sm border border-primary-100 rounded-2xl p-8 hover:shadow-soft-lg hover:border-accent-200 transition-all duration-300 group">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h2 class="text-2xl font-bold text-primary-900 group-hover:text-accent-700 transition-colors">
                                            {{$word["word"]}}
                                        </h2>
                                        @if($word["part_of_speech"])
                                            <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-semibold bg-accent-100 text-accent-700 border border-accent-200">
                                                {{$word["part_of_speech"]}}
                                            </span>
                                        @endif
                                    </div>
                                    @if($word["pronunciation"] || $word["pronunciation_katakana"])
                                        <div class="flex flex-wrap items-center gap-2 text-sm text-primary-600">
                                            @if($word["pronunciation"])
                                                <span class="font-mono text-primary-700">{{$word["pronunciation"]}}</span>
                                            @endif
                                            @if($word["pronunciation_katakana"])
                                                @if($word["pronunciation"])
                                                    <span class="text-primary-400">|</span>
                                                @endif
                                                <span class="text-primary-600">{{$word["pronunciation_katakana"]}}</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                @hasanyrole('membership')
                                    <form method="post" action="{{route('DeleteWord')}}" onsubmit="return confirmDelete()">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="id" value="{{$word["id"]}}">
                                        <button type="submit"
                                            class="text-primary-300 hover:text-red-600 transition-colors p-2 rounded-lg hover:bg-red-50">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                @endhasanyrole
                            </div>

                            <div class="border-t border-primary-100 pt-4 mb-5">
                                <div class="space-y-2">
                                    @foreach($word->japanese as $ja_words)
                                        <div class="flex items-start">
                                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-accent-500 mt-2 mr-3 flex-shrink-0"></span>
                                            <p class="text-primary-800 text-base">{{$ja_words["japanese"]}}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            @if($word["en_example"] || $word["jp_example"])
                                <div class="bg-gradient-to-br from-primary-50 to-accent-50/30 rounded-xl p-5 space-y-3 border border-primary-100">
                                    <div class="flex items-center mb-2">
                                        <svg class="w-4 h-4 mr-2 text-accent-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                        </svg>
                                        <span class="text-xs font-semibold text-primary-700">例文</span>
                                    </div>
                                    @if($word["en_example"])
                                        <p class="text-primary-700 italic leading-relaxed">{{$word["en_example"]}}</p>
                                    @endif
                                    @if($word["jp_example"])
                                        <p class="text-primary-600 leading-relaxed">{{$word["jp_example"]}}</p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    </div>

<script>
    const ADD_MEANING_BTN = document.getElementById('add_meaning');
    const AUTOCOMPLETE_BTN = document.getElementById('autocomplete_btn');
    const LOADING_MESSAGE = document.getElementById('loading_message');
    const PREVIEW_AREA = document.getElementById('preview_area');
    const MANUAL_MEANINGS = document.getElementById('manual_meanings');
    const MEANINGS_CONTAINER = document.getElementById('meanings_container');
    const form = document.getElementById('add_form');
    let count = 2;

    // 意味追加ボタンのハンドラー
    ADD_MEANING_BTN.addEventListener('click', () => {
        const div = document.createElement('div');
        div.innerHTML = `
            <label for="jp_word_${count}" class="block text-sm font-semibold text-primary-900 mb-2">
                意味 ${count}
            </label>
            <input type="text" id="jp_word_${count}" name="meaningArray[]" placeholder="意味を入力"
                class="w-full border-2 border-primary-200 rounded-xl px-5 py-3 focus:outline-none focus:border-accent-500 focus:ring-4 focus:ring-accent-100 transition-all duration-200 bg-white/50 backdrop-blur-sm text-primary-900 placeholder-primary-400">
        `;
        form.insertBefore(div, ADD_MEANING_BTN);
        count++;
    });

    // 補完ボタンのハンドラー
    AUTOCOMPLETE_BTN.addEventListener('click', async () => {
        const word = document.getElementById('word').value.trim();
        const context = document.getElementById('context').value.trim();

        if (!word) {
            alert('英単語を入力してください');
            return;
        }

        // UIをリセット
        PREVIEW_AREA.style.display = 'none';
        MANUAL_MEANINGS.style.display = 'none';

        // ローディング表示
        LOADING_MESSAGE.style.display = 'block';
        LOADING_MESSAGE.textContent = '辞書から単語情報を取得しています...';
        AUTOCOMPLETE_BTN.disabled = true;

        try {
            // APIリクエスト
            const response = await fetch('/word/autocomplete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ word, context })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || '補完に失敗しました');
            }

            // AIローディングメッセージ
            LOADING_MESSAGE.textContent = 'AI が日本語訳を生成しています...';

            // 結果が返ってきたら表示
            if (data.success) {
                displayPreview(data.data);
            } else {
                throw new Error(data.message || '補完に失敗しました');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('エラーが発生しました: ' + error.message + '\n\n手動で意味を入力してください。');
            MANUAL_MEANINGS.style.display = 'block';
        } finally {
            LOADING_MESSAGE.style.display = 'none';
            AUTOCOMPLETE_BTN.disabled = false;
        }
    });

    // プレビュー表示関数
    function displayPreview(data) {
        // 品詞・発音を表示
        document.getElementById('part_of_speech').value = data.part_of_speech || '';
        document.getElementById('pronunciation').value = data.pronunciation || '';
        document.getElementById('pronunciation_katakana').value = data.pronunciation_katakana || '';

        // 意味をクリア
        MEANINGS_CONTAINER.innerHTML = '';

        // 意味を表示（編集可能）
        if (data.meanings && data.meanings.length > 0) {
            data.meanings.forEach((meaning, index) => {
                const meaningDiv = document.createElement('div');
                meaningDiv.className = 'flex gap-2';
                meaningDiv.innerHTML = `
                    <input type="text" name="meaningArray[]" value="${escapeHtml(meaning)}"
                        class="flex-1 border-2 border-primary-200 rounded-xl px-5 py-3 focus:outline-none focus:border-accent-500 focus:ring-4 focus:ring-accent-100 transition-all duration-200 bg-white text-primary-900">
                    ${index > 0 ? `
                    <button type="button" onclick="this.parentElement.remove()"
                        class="text-red-500 hover:text-red-700 px-3 py-2 rounded-xl hover:bg-red-50 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                    ` : ''}
                `;
                MEANINGS_CONTAINER.appendChild(meaningDiv);
            });

            // 意味追加ボタンを追加
            const addBtn = document.createElement('button');
            addBtn.type = 'button';
            addBtn.className = 'inline-flex items-center text-sm text-accent-700 hover:text-accent-800 font-semibold transition-colors mt-2';
            addBtn.innerHTML = `
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                意味を追加
            `;
            addBtn.addEventListener('click', () => {
                const newMeaningDiv = document.createElement('div');
                newMeaningDiv.className = 'flex gap-2';
                newMeaningDiv.innerHTML = `
                    <input type="text" name="meaningArray[]" placeholder="意味を入力"
                        class="flex-1 border-2 border-primary-200 rounded-xl px-5 py-3 focus:outline-none focus:border-accent-500 focus:ring-4 focus:ring-accent-100 transition-all duration-200 bg-white text-primary-900">
                    <button type="button" onclick="this.parentElement.remove()"
                        class="text-red-500 hover:text-red-700 px-3 py-2 rounded-xl hover:bg-red-50 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                `;
                MEANINGS_CONTAINER.insertBefore(newMeaningDiv, addBtn);
            });
            MEANINGS_CONTAINER.appendChild(addBtn);
        }

        // 例文があれば表示
        if (data.en_example) {
            document.getElementById('en_example').value = data.en_example;
        }
        if (data.jp_example) {
            document.getElementById('jp_example').value = data.jp_example;
        }

        // プレビューエリアを表示
        PREVIEW_AREA.style.display = 'block';
    }

    // HTMLエスケープ関数
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }

    function confirmDelete() {
        return confirm('本当にこの単語を削除しますか？');
    }
</script>
</x-template>
