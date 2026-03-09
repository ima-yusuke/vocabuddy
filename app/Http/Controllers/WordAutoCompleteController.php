<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WordAutoCompleteController extends Controller
{
    /**
     * 英単語の自動補完
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function autocomplete(Request $request)
    {
        $validated = $request->validate([
            'word' => 'required|string|max:100',
            'context' => 'nullable|string|max:500',
        ]);

        $word = $validated['word'];
        $context = $validated['context'] ?? null;

        try {
            // Step 1: Free Dictionary APIで基本情報を取得
            $dictionaryData = $this->fetchDictionaryData($word);

            // Step 2: Gemini APIで整形
            $aiData = $this->formatWithAI($word, $context, $dictionaryData);

            return response()->json([
                'success' => true,
                'data' => $aiData
            ]);

        } catch (\Exception $e) {
            Log::error('Autocomplete error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'エラーが発生しました: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Free Dictionary APIからデータ取得
     */
    private function fetchDictionaryData(string $word): ?array
    {
        try {
            $response = Http::timeout(10)->get(
                "https://api.dictionaryapi.dev/api/v2/entries/en/{$word}"
            );

            if ($response->successful()) {
                $data = $response->json();

                if (is_array($data) && count($data) > 0) {
                    $entry = $data[0];

                    return [
                        'word' => $entry['word'] ?? $word,
                        'phonetics' => $entry['phonetics'] ?? [],
                        'meanings' => $entry['meanings'] ?? [],
                    ];
                }
            }

            // 404 or other errors - return null
            Log::info("Dictionary API: Word '{$word}' not found or error occurred");
            return null;

        } catch (\Exception $e) {
            Log::warning("Dictionary API exception: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Gemini APIで整形
     */
    private function formatWithAI(string $word, ?string $context, ?array $dictionaryData): array
    {
        $apiKey = config('services.gemini.api_key');

        if (!$apiKey) {
            throw new \Exception('Gemini API キーが設定されていません。.envファイルにGEMINI_API_KEYを追加してください。');
        }

        // プロンプト作成
        $prompt = $this->buildPrompt($word, $context, $dictionaryData);

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

                // JSONを抽出してパース
                return $this->parseAIResponse($generatedText);
            } else {
                throw new \Exception('Gemini API request failed: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Gemini API error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * プロンプト作成
     */
    private function buildPrompt(string $word, ?string $context, ?array $dictionaryData): string
    {
        $prompt = "あなたは日本人英語学習者向けの単語帳アシスタントです。\n\n";
        $prompt .= "英単語: {$word}\n";

        if ($context) {
            $prompt .= "文脈: {$context}\n";
        }

        if ($dictionaryData) {
            $prompt .= "\n辞書データ:\n";
            $prompt .= json_encode($dictionaryData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }

        $prompt .= "\n\n以下の形式でJSON出力してください：\n";
        $prompt .= "{\n";
        $prompt .= '  "part_of_speech": "日本語の品詞（名詞/動詞/形容詞など）",' . "\n";
        $prompt .= '  "pronunciation_katakana": "カタカナ読み",' . "\n";
        $prompt .= '  "meanings": ["意味1", "意味2"],' . "\n";
        $prompt .= '  "en_example": "短く自然な会話例文",' . "\n";
        $prompt .= '  "jp_example": "自然な日本語訳"';

        if ($context) {
            $prompt .= ',' . "\n";
            $prompt .= '  "context_meaning": "文脈での意味"';
        }

        $prompt .= "\n}\n\n";

        $prompt .= "重要:\n";
        if ($context) {
            $prompt .= "- 文脈がある場合、その文脈での意味を優先し、context_meaningに記載\n";
        }
        $prompt .= "- 意味は日本人学習者向けに自然な日本語で（辞書的すぎない）\n";
        $prompt .= "- 例文は短く会話的に（InstagramやLINEで使えそうな感じ）\n";
        $prompt .= "- 難しい説明は避けて簡潔に\n";
        $prompt .= "- 必ずJSON形式のみで出力（マークダウンコードブロックは不要）\n";

        return $prompt;
    }

    /**
     * AIレスポンスからJSONをパース
     */
    private function parseAIResponse(string $text): array
    {
        // JSONブロックを抽出（```jsonで囲まれている場合）
        if (preg_match('/```json\s*(\{.*?\})\s*```/s', $text, $matches)) {
            $jsonText = $matches[1];
        } elseif (preg_match('/(\{.*?\})/s', $text, $matches)) {
            $jsonText = $matches[1];
        } else {
            throw new \Exception('JSON形式のレスポンスが見つかりませんでした');
        }

        $data = json_decode($jsonText, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('JSONのパースに失敗しました: ' . json_last_error_msg());
        }

        // 必須フィールドの確認
        if (!isset($data['meanings']) || !is_array($data['meanings'])) {
            throw new \Exception('意味(meanings)が見つかりませんでした');
        }

        return $data;
    }
}
