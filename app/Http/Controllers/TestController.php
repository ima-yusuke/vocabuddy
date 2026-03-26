<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Word;
use App\Models\WeakWord;

class TestController extends Controller
{
    public function ShowTestStart()
    {
        // セッションをクリア
        session()->forget('test_questions');
        session()->forget('test_total_count');

        return view('test-start');
    }

    public function StartTest(Request $request)
    {
        $count = $request->input('count', 10);

        \Log::info('=== TEST START ===', ['requested_count' => $count]);

        // 問題を一括生成
        $questionQueue = $this->generateQuestionBatch($count);

        \Log::info('Question generation result', [
            'generated_count' => count($questionQueue),
            'expected_count' => $count
        ]);

        if (empty($questionQueue)) {
            \Log::error('Failed to generate any questions');
            return redirect()->route('ShowTest')->with('error', '問題の生成に失敗しました');
        }

        session([
            'test_questions' => $questionQueue,
            'test_total_count' => $count
        ]);

        return redirect()->route('ShowQuestion');
    }

    public function ShowQuestion()
    {
        // セッションから問題リストを取得
        $questionQueue = session('test_questions', []);

        if (empty($questionQueue)) {
            return redirect()->route('ShowTest');
        }

        // 最初の問題を取り出す
        $currentQuestion = array_shift($questionQueue);

        // 残りの問題をセッションに保存
        session(['test_questions' => $questionQueue]);

        // ビューに渡すデータを準備
        $correctWord = $currentQuestion['word'];
        $correctMeaning = $currentQuestion['correct_meaning'];
        $options = collect($currentQuestion['options']);
        $remainingQuestions = count($questionQueue);
        $totalCount = session('test_total_count', 10);
        $currentQuestionNumber = $totalCount - $remainingQuestions;

        return view('test', compact('correctWord', 'correctMeaning', 'options', 'remainingQuestions', 'currentQuestionNumber', 'totalCount'));
    }

    private function generateQuestionBatch($count = 10)
    {
        \Log::info('=== generateQuestionBatch START ===', ['count' => $count]);

        $userId = auth()->id();
        $words = Word::with('japanese')->where('user_id', $userId)->get();

        \Log::info('Words fetched', ['total_words' => $words->count()]);

        if ($words->count() < 1) {
            \Log::error('No words found in database');
            return [];
        }

        // 最大指定問題数（または単語数が少ない場合はその数）
        $questionCount = min($count, $words->count());

        // 苦手単語を優先的に出題
        $weakWordIds = WeakWord::where('user_id', $userId)
            ->pluck('word_id')
            ->toArray();

        \Log::info('Weak words', ['weak_word_count' => count($weakWordIds)]);

        $selectedWords = collect();

        if (count($weakWordIds) > 0) {
            // 苦手単語がある場合、問題の60%を苦手単語から出題
            $weakWordCount = min(ceil($questionCount * 0.6), count($weakWordIds));
            $weakWords = $words->whereIn('id', $weakWordIds)->random(min($weakWordCount, count($weakWordIds)));
            $selectedWords = $selectedWords->merge($weakWords);

            \Log::info('Weak words selected', ['selected_count' => $weakWords->count()]);
        }

        // 残りをランダムに選択
        $remainingCount = $questionCount - $selectedWords->count();
        if ($remainingCount > 0) {
            $remainingWords = $words->whereNotIn('id', $selectedWords->pluck('id'))
                ->random(min($remainingCount, $words->whereNotIn('id', $selectedWords->pluck('id'))->count()));
            $selectedWords = $selectedWords->merge($remainingWords);

            \Log::info('Random words selected', ['selected_count' => $remainingWords->count()]);
        }

        // シャッフルして順序をランダムに
        $selectedWords = $selectedWords->shuffle();

        \Log::info('Words selected for questions', ['selected_count' => $selectedWords->count()]);

        // プランに応じたモデル名を取得
        $modelName = auth()->user()->getAiModelName('gemini-3-flash-preview');

        // 1回のAPI呼び出しで全問題を生成
        $apiKey = config('services.gemini.api_key');
        $questions = [];

        \Log::info('API Key check', ['has_key' => !empty($apiKey), 'model' => $modelName]);

        if ($apiKey) {
            // プロンプト用に単語リストを作成
            $wordList = [];
            foreach ($selectedWords as $word) {
                $wordList[] = [
                    'english' => $word->word,
                    'japanese' => $word->japanese->first()->japanese
                ];
            }

            $wordListText = '';
            foreach ($wordList as $index => $item) {
                $wordListText .= ($index + 1) . ". {$item['english']} - {$item['japanese']}\n";
            }

            $prompt = "以下の英単語リストについて、それぞれ4択クイズの紛らわしい不正解選択肢を3つずつ作成してください。

【単語リスト】
{$wordListText}

要件:
- 各単語の正解の意味に似ているが微妙に違う日本語の意味を3つ
- 英語学習者が間違えやすい、似た意味を選ぶこと
- 各選択肢は15文字以内の簡潔な日本語で

以下の形式で出力してください:

1. {$wordList[0]['english']}
1-1. (不正解1)
1-2. (不正解2)
1-3. (不正解3)

2. {$wordList[1]['english']}
2-1. (不正解1)
2-2. (不正解2)
2-3. (不正解3)

（以下同様に全ての単語について）";

            try {
                \Log::info('=== Calling Gemini API for batch test generation ===');
                \Log::info('API URL', ['url' => "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent"]);
                \Log::info('Prompt preview', ['prompt_length' => strlen($prompt), 'word_count' => $selectedWords->count()]);

                $response = Http::timeout(30)->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key={$apiKey}",
                    [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $prompt]
                                ]
                            ]
                        ]
                    ]
                );

                \Log::info('API Response Status', ['status' => $response->status()]);

                if ($response->successful()) {
                    $result = $response->json();
                    $generatedText = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';

                    \Log::info('Generated batch test options', [
                        'text_length' => strlen($generatedText),
                        'first_200_chars' => substr($generatedText, 0, 200)
                    ]);

                    // 各単語の選択肢を抽出
                    foreach ($selectedWords as $index => $word) {
                        $correctMeaning = $word->japanese->first()->japanese;
                        $wordNum = $index + 1;

                        // この単語の不正解選択肢を抽出
                        $pattern = "/{$wordNum}\.\s+.*?\n{$wordNum}-1\.\s*(.+?)\n{$wordNum}-2\.\s*(.+?)\n{$wordNum}-3\.\s*(.+?)(\n|$)/s";

                        \Log::info("Attempting pattern match for word {$wordNum}", ['word' => $word->word]);

                        if (preg_match($pattern, $generatedText, $matches)) {
                            $wrongOptions = [
                                trim($matches[1]),
                                trim($matches[2]),
                                trim($matches[3])
                            ];

                            \Log::info("Successfully matched options for word {$wordNum}", ['options' => $wrongOptions]);

                            $options = collect([$correctMeaning])->merge($wrongOptions)->shuffle();

                            $questions[] = [
                                'word' => $word,
                                'correct_meaning' => $correctMeaning,
                                'options' => $options->toArray()
                            ];
                        } else {
                            \Log::warning("Failed to match pattern for word {$wordNum}", ['word' => $word->word]);
                        }
                    }

                    \Log::info('API generation completed', ['questions_generated' => count($questions)]);
                } else {
                    \Log::error('API request failed', ['status' => $response->status(), 'body' => $response->body()]);
                }
            } catch (\Exception $e) {
                \Log::error('Gemini API batch generation error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            }
        } else {
            \Log::warning('Skipping API call - no API key configured');
        }

        // API生成に失敗した場合は従来の方法で補完
        if (count($questions) < $questionCount && $words->count() >= 4) {
            \Log::warning('Falling back to traditional method', [
                'api_generated' => count($questions),
                'needed' => $questionCount
            ]);

            $remainingWords = $selectedWords->slice(count($questions));

            foreach ($remainingWords as $word) {
                $correctMeaning = $word->japanese->first()->japanese;
                $wrongWords = $words->where('id', '!=', $word->id)->random(min(3, $words->count() - 1));
                $wrongMeanings = $wrongWords->map(function($w) {
                    return $w->japanese->first()->japanese;
                });
                $options = collect([$correctMeaning])->merge($wrongMeanings)->shuffle();

                $questions[] = [
                    'word' => $word,
                    'correct_meaning' => $correctMeaning,
                    'options' => $options->toArray()
                ];
            }
        }

        \Log::info('=== generateQuestionBatch END ===', ['final_question_count' => count($questions)]);

        return $questions;
    }

    private function generateSimilarOptions($englishWord, $correctMeaning)
    {
        $apiKey = config('services.gemini.api_key');

        if (!$apiKey) {
            \Log::error('Gemini API key not found for test generation');
            return [];
        }

        $prompt = "英単語「{$englishWord}」の日本語の意味は「{$correctMeaning}」です。

この単語の4択クイズを作成したいので、紛らわしい不正解の選択肢を3つ作成してください。

要件:
1. 正解の意味「{$correctMeaning}」に似ているが微妙に違う日本語の意味を3つ
2. 英語学習者が間違えやすい、似た意味の単語の意味を選ぶこと
3. 完全に無関係な意味は避けること
4. 各選択肢は15文字以内の簡潔な日本語で

以下の形式で出力してください（番号と改行のみ、他の説明は不要）:

1. (不正解の選択肢1)
2. (不正解の選択肢2)
3. (不正解の選択肢3)";

        try {
            \Log::info('Calling Gemini API for test generation', ['word' => $englishWord]);

            $response = Http::timeout(15)->post(
                "https://generativelanguage.googleapis.com/v1beta/models/gemini-3-flash-preview:generateContent?key={$apiKey}",
                [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ]
                ]
            );

            \Log::info('Gemini API response status', ['status' => $response->status()]);

            if ($response->successful()) {
                $result = $response->json();
                $generatedText = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';

                \Log::info('Generated text from API', ['text' => $generatedText]);

                // 選択肢を抽出
                $wrongOptions = [];
                if (preg_match_all('/^\d+\.\s*(.+)$/m', $generatedText, $matches)) {
                    $wrongOptions = array_slice($matches[1], 0, 3);
                }

                \Log::info('Extracted wrong options', ['options' => $wrongOptions, 'count' => count($wrongOptions)]);

                // 正解と不正解をシャッフル
                if (count($wrongOptions) === 3) {
                    return collect([$correctMeaning])->merge($wrongOptions)->shuffle();
                } else {
                    \Log::warning('Not enough options generated', ['count' => count($wrongOptions)]);
                }
            } else {
                \Log::error('Gemini API request failed', ['status' => $response->status(), 'body' => $response->body()]);
            }
        } catch (\Exception $e) {
            \Log::error('Gemini API error in test generation: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }

        return [];
    }

    public function CheckAnswer(Request $request)
    {
        $selectedAnswer = $request->input('answer');
        $correctAnswer = $request->input('correct_answer');
        $wordId = $request->input('word_id');

        $isCorrect = $selectedAnswer === $correctAnswer;

        $word = Word::with('japanese')->findOrFail($wordId);

        // 苦手単語の記録を更新
        $this->updateWeakWord(auth()->id(), $wordId, $isCorrect);

        $remainingQuestions = count(session('test_questions', []));
        $hasMoreQuestions = $remainingQuestions > 0;

        return view('test-result', compact('isCorrect', 'selectedAnswer', 'correctAnswer', 'word', 'hasMoreQuestions'));
    }

    /**
     * 苦手単語の記録を更新
     */
    private function updateWeakWord($userId, $wordId, $isCorrect)
    {
        $weakWord = WeakWord::where('user_id', $userId)
            ->where('word_id', $wordId)
            ->first();

        if ($isCorrect) {
            // 正解の場合
            if ($weakWord) {
                $weakWord->recordCorrect(); // 連続正解をカウント、3回で削除
            }
            // 苦手単語でない場合は何もしない
        } else {
            // 不正解の場合
            if ($weakWord) {
                $weakWord->recordIncorrect(); // 連続正解をリセット
            } else {
                // 初めて間違えた場合は新規作成
                WeakWord::create([
                    'user_id' => $userId,
                    'word_id' => $wordId,
                    'incorrect_count' => 1,
                    'consecutive_correct_count' => 0,
                    'last_incorrect_at' => now(),
                ]);
            }
        }
    }
}
