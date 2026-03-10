<x-template title="単語を編集">
    <div class="min-h-screen bg-gradient-to-br from-primary-50 via-white to-accent-50">
        <section class="py-16 px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto">
                <div class="bg-white/80 backdrop-blur-sm border border-primary-100 rounded-2xl p-10 shadow-soft-lg">
                    <div class="flex items-center mb-8">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-accent-400 to-accent-600 flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-primary-900">
                            単語を編集
                        </h2>
                    </div>

                    <form method="post" action="{{route('UpdateWord', $word->id)}}" class="space-y-6" id="edit_form">
                        @csrf
                        @method('PATCH')

                        <!-- Hidden fields for part_of_speech and pronunciation_katakana -->
                        <input type="hidden" name="part_of_speech" id="part_of_speech_hidden" value="{{$word->part_of_speech}}">
                        <input type="hidden" name="pronunciation_katakana" id="pronunciation_katakana_hidden" value="{{$word->pronunciation_katakana}}">

                        <div>
                            <label for="word" class="block text-sm font-semibold text-primary-900 mb-2">
                                英単語
                            </label>
                            <input type="text" id="word" name="word" value="{{$word->word}}" placeholder="例: serendipity"
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
                            class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5">
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
                            @foreach($word->japanese as $index => $japanese)
                                <div>
                                    <label for="jp_word_{{$index + 1}}" class="block text-sm font-semibold text-primary-900 mb-2">
                                        意味 {{$index + 1}}
                                    </label>
                                    <input type="text" id="jp_word_{{$index + 1}}" name="meaningArray[]" value="{{$japanese->japanese}}" placeholder="意味を入力"
                                        class="w-full border-2 border-primary-200 rounded-xl px-5 py-3 focus:outline-none focus:border-accent-500 focus:ring-4 focus:ring-accent-100 transition-all duration-200 bg-white/50 backdrop-blur-sm text-primary-900 placeholder-primary-400">
                                </div>
                            @endforeach

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
                                class="w-full border-2 border-primary-200 rounded-xl px-5 py-3 focus:outline-none focus:border-accent-500 focus:ring-4 focus:ring-accent-100 resize-none transition-all duration-200 bg-white/50 backdrop-blur-sm text-primary-900 placeholder-primary-400" rows="3">{{$word->en_example}}</textarea>
                        </div>

                        <div>
                            <label for="jp_example" class="block text-sm font-semibold text-primary-900 mb-2">
                                例文（日本語）
                            </label>
                            <textarea id="jp_example" name="jp_example" placeholder="例: カフェで会ったのは純粋な偶然だった。"
                                class="w-full border-2 border-primary-200 rounded-xl px-5 py-3 focus:outline-none focus:border-accent-500 focus:ring-4 focus:ring-accent-100 resize-none transition-all duration-200 bg-white/50 backdrop-blur-sm text-primary-900 placeholder-primary-400" rows="3">{{$word->jp_example}}</textarea>
                        </div>

                        <div class="flex gap-4">
                            <button type="submit"
                                class="flex-1 bg-gradient-to-r from-primary-800 to-primary-900 hover:from-primary-900 hover:to-primary-800 text-white px-6 py-4 rounded-xl font-semibold shadow-soft hover:shadow-soft-lg transition-all duration-300 transform hover:-translate-y-0.5">
                                更新する
                            </button>
                            <a href="{{route('ShowIndex')}}"
                                class="flex-1 text-center bg-white hover:bg-primary-50 border-2 border-primary-200 text-primary-900 px-6 py-4 rounded-xl font-semibold shadow-soft hover:shadow-soft-lg transition-all duration-300">
                                キャンセル
                            </a>
                        </div>
                    </form>
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
    const form = document.getElementById('edit_form');
    let count = {{count($word->japanese) + 1}};

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
                // エラーの種類に応じた処理
                const errorMessage = data.error || '補完に失敗しました';
                const errorType = data.error_type || 'general';

                console.error('Autocomplete error:', {
                    type: errorType,
                    message: errorMessage,
                    status: response.status
                });

                // エラータイプに応じたメッセージ表示
                let userMessage = errorMessage;
                let suggestion = '';

                switch (errorType) {
                    case 'api_key_missing':
                        suggestion = '\n\n管理者に問い合わせてください。';
                        break;
                    case 'timeout':
                        suggestion = '\n\nしばらく待ってから再度お試しください。';
                        break;
                    case 'parse_error':
                        suggestion = '\n\n手動で意味を入力してください。';
                        break;
                    default:
                        if (!context) {
                            suggestion = '\n\n文脈を追加すると精度が上がる場合があります。';
                        } else {
                            suggestion = '\n\n手動で意味を入力してください。';
                        }
                }

                throw new Error(userMessage + suggestion);
            }

            // AIローディングメッセージ
            LOADING_MESSAGE.textContent = 'AI が日本語訳を生成しています...';

            // 結果が返ってきたら表示
            if (data.success) {
                displayPreview(data.data);
            } else {
                throw new Error(data.error || '補完に失敗しました');
            }
        } catch (error) {
            console.error('Error details:', {
                message: error.message,
                stack: error.stack,
                word: word,
                hasContext: !!context
            });

            // エラーメッセージ表示
            alert('エラーが発生しました: ' + error.message);

            // 手動入力フォームを表示
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
        document.getElementById('pronunciation_katakana').value = data.pronunciation_katakana || '';

        // Hidden fieldsにも値を設定
        document.getElementById('part_of_speech_hidden').value = data.part_of_speech || '';
        document.getElementById('pronunciation_katakana_hidden').value = data.pronunciation_katakana || '';

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
</script>
</x-template>
