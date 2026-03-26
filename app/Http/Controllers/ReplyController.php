<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Word;
use App\Models\ReplyTemplate;
use App\Services\GeminiEmbeddingService;

class ReplyController extends Controller
{
    public function ShowReplyAssistant()
    {
        return view('reply-assistant');
    }

    /**
     * 返信履歴一覧を表示
     */
    public function ShowHistory(Request $request)
    {
        $category = $request->input('category');

        $query = ReplyTemplate::where('user_id', auth()->id())
            ->orderBy('times_used', 'desc')
            ->orderBy('created_at', 'desc');

        if ($category) {
            $query->where('category', $category);
        }

        $templates = $query->get();

        return view('reply-history', compact('templates', 'category'));
    }

    /**
     * 類似した返信テンプレートを検索
     */
    public function FindSimilarReplies(Request $request)
    {
        $friendMessage = $request->input('friend_message');
        $replyIntent = $request->input('reply_intent');

        if (empty($friendMessage) || empty($replyIntent)) {
            return response()->json(['similar_replies' => []]);
        }

        // Embeddingを生成
        $embeddingService = new GeminiEmbeddingService();
        $searchQuery = $embeddingService->createSearchQuery($friendMessage, $replyIntent);
        $queryEmbedding = $embeddingService->generateEmbedding($searchQuery);

        if (!$queryEmbedding) {
            return response()->json(['similar_replies' => []]);
        }

        // 類似した返信を検索（閾値0.85以上）
        $similarReplies = ReplyTemplate::findSimilar(auth()->id(), $queryEmbedding, 0.85);

        // 上位3件まで返す
        $results = $similarReplies->take(3)->map(function ($template) {
            return [
                'id' => $template->id,
                'reply_en' => $template->reply_en,
                'reply_ja' => $template->reply_ja,
                'times_used' => $template->times_used,
                'similarity_score' => round($template->similarity_score * 100, 1),
            ];
        });

        return response()->json(['similar_replies' => $results]);
    }

    public function GenerateReply(Request $request)
    {
        $friendMessage = $request->input('friend_message');
        $replyIntent = $request->input('reply_intent');
        $relationship = $request->input('relationship');

        // 単語帳の全単語を取得
        $words = Word::with('japanese')->get();

        // 単語帳の単語リストを作成（英単語のみ）
        $wordList = $words->map(function($word) {
            return $word->word;
        })->implode(', ');

        // Gemini APIキーを取得
        $apiKey = config('services.gemini.api_key');

        if (!$apiKey) {
            return back()->with('error', 'Gemini API キーが設定されていません。.envファイルにGEMINI_API_KEYを追加してください。');
        }

        // 関係性に応じたトーンの説明
        $toneGuide = [
            'friend' => '友達同士のカジュアルでフレンドリーな表現',
            'work' => '仕事関係者への丁寧でプロフェッショナルな表現',
            'romantic' => '恋人への温かく親密な表現',
            'family' => '家族への親しみやすく温かい表現'
        ];

        $selectedTone = $toneGuide[$relationship] ?? $toneGuide['friend'];

        // Gemini APIにリクエスト
        $prompt = "あなたは英語でのメッセージ作成をサポートするアシスタントです。

相手との関係性: {$selectedTone}

相手からのメッセージ: \"{$friendMessage}\"

ユーザーの返信したい内容: \"{$replyIntent}\"

以下の英単語リストの中から、自然に使えそうなものがあれば1-2個程度使って返信文を作成してください。
これらの単語の意味やニュアンス、イディオム、様々な使い方を考慮して、最も自然な形で使用してください。

【単語リスト】
{$wordList}

重要な要件:
1. 最優先: {$selectedTone}にふさわしい自然な会話表現
2. 相手のメッセージに対する適切な返信
3. ユーザーの返信意図を正確に反映
4. 単語リストの単語は無理に使わず、自然に使える場合のみ1-2個程度使用
5. 単語の持つ様々な意味やイディオム表現も考慮して使用
6. 短くてシンプルな文章（長すぎないこと）

返信文とその日本語訳、使用した単語を以下の形式で出力してください:

【英語返信】
(返信文)

【日本語訳】
(日本語訳)

【使用した単語帳の単語】
(使用した場合のみ: 単語1, 単語2)";

        try {
            $response = Http::timeout(30)->post(
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

            if ($response->successful()) {
                $result = $response->json();
                $generatedText = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';

                // 英語返信部分を抽出
                $englishReply = '';
                if (preg_match('/【英語返信】\s*\n(.+?)(\n|$)/s', $generatedText, $matches)) {
                    $englishReply = trim($matches[1]);
                } else if (preg_match('/英語返信[:\s]*\n(.+?)(\n|$)/s', $generatedText, $matches)) {
                    $englishReply = trim($matches[1]);
                }

                // 使用した単語を抽出（AIが【使用した単語帳の単語】セクションに明記した単語のみ）
                $usedWords = [];

                // 【使用した単語帳の単語】セクションを抽出
                $usedWordsSection = '';
                if (preg_match('/【使用した単語帳の単語】\s*\n(.+?)(\n\n|$)/s', $generatedText, $matches)) {
                    $usedWordsSection = $matches[1];
                } else if (preg_match('/使用した単語帳の単語[:\s]*\n(.+?)(\n\n|$)/s', $generatedText, $matches)) {
                    $usedWordsSection = $matches[1];
                }

                // セクションから単語を検出
                if (!empty($usedWordsSection)) {
                    foreach ($words as $word) {
                        // 単語境界を考慮した正確な一致（大文字小文字無視）
                        if (preg_match('/\b' . preg_quote($word->word, '/') . '\b/i', $usedWordsSection)) {
                            $usedWords[] = $word;
                        }
                    }
                }

                // 日本語訳を抽出
                $japaneseTranslation = '';
                if (preg_match('/【日本語訳】\s*\n(.+?)(\n|$)/s', $generatedText, $matches)) {
                    $japaneseTranslation = trim($matches[1]);
                } else if (preg_match('/日本語訳[:\s]*\n(.+?)(\n|$)/s', $generatedText, $matches)) {
                    $japaneseTranslation = trim($matches[1]);
                }

                // 履歴として保存
                $vocabIds = collect($usedWords)->pluck('id')->toArray();

                // Embeddingを生成
                $embeddingService = new GeminiEmbeddingService();
                $searchQuery = $embeddingService->createSearchQuery($friendMessage, $replyIntent);
                $embedding = $embeddingService->generateEmbedding($searchQuery);

                ReplyTemplate::create([
                    'user_id' => auth()->id(),
                    'category' => $relationship,
                    'partner_message' => $friendMessage,
                    'intent_ja' => $replyIntent,
                    'reply_en' => $englishReply,
                    'reply_ja' => $japaneseTranslation,
                    'vocab_ids' => $vocabIds,
                    'times_used' => 0,
                    'embedding' => $embedding,
                ]);

                return view('reply-result', compact('friendMessage', 'replyIntent', 'generatedText', 'englishReply', 'usedWords'));
            } else {
                return back()->with('error', 'APIリクエストに失敗しました: ' . $response->body());
            }
        } catch (\Exception $e) {
            return back()->with('error', 'エラーが発生しました: ' . $e->getMessage());
        }
    }
}
