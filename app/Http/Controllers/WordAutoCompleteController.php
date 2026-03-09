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

        Log::info('Autocomplete request', ['word' => $word, 'has_context' => !is_null($context)]);

        try {
            // Step 1: Free Dictionary APIで基本情報を取得
            $dictionaryData = $this->fetchDictionaryData($word);

            // Step 2: Gemini APIで整形
            $aiData = $this->formatWithAI($word, $context, $dictionaryData);

            Log::info('Autocomplete successful', ['word' => $word]);

            return response()->json([
                'success' => true,
                'data' => $aiData
            ]);

        } catch (\Exception $e) {
            Log::error('Autocomplete error', [
                'word' => $word,
                'has_context' => !is_null($context),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // エラーメッセージを分類
            $errorMessage = $e->getMessage();
            $errorType = 'general';

            if (strpos($errorMessage, 'APIキーが設定されていません') !== false) {
                $errorType = 'api_key_missing';
            } elseif (strpos($errorMessage, 'timeout') !== false || strpos($errorMessage, 'timed out') !== false) {
                $errorType = 'timeout';
            } elseif (strpos($errorMessage, 'JSON') !== false || strpos($errorMessage, 'パース') !== false) {
                $errorType = 'parse_error';
            }

            return response()->json([
                'success' => false,
                'error' => $errorMessage,
                'error_type' => $errorType
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

                    Log::info('Dictionary API success', ['word' => $word]);

                    return [
                        'word' => $entry['word'] ?? $word,
                        'phonetics' => $entry['phonetics'] ?? [],
                        'meanings' => $entry['meanings'] ?? [],
                    ];
                }
            }

            // 404 or other errors - return null
            if ($response->status() === 404) {
                Log::warning('Dictionary API: Word not found', ['word' => $word, 'status' => 404]);
            } else {
                Log::warning('Dictionary API: Unexpected response', [
                    'word' => $word,
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }
            return null;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::warning('Dictionary API: Network error', [
                'word' => $word,
                'error' => $e->getMessage()
            ]);
            return null;
        } catch (\Illuminate\Http\Client\RequestException $e) {
            if (strpos($e->getMessage(), 'timeout') !== false || strpos($e->getMessage(), 'timed out') !== false) {
                Log::warning('Dictionary API: Timeout', [
                    'word' => $word,
                    'error' => $e->getMessage()
                ]);
            } else {
                Log::warning('Dictionary API: Request failed', [
                    'word' => $word,
                    'error' => $e->getMessage()
                ]);
            }
            return null;
        } catch (\Exception $e) {
            Log::warning('Dictionary API: Unexpected exception', [
                'word' => $word,
                'error' => $e->getMessage(),
                'type' => get_class($e)
            ]);
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
            Log::error('Gemini API: API key not configured', ['word' => $word]);
            throw new \Exception('APIキーが設定されていません');
        }

        // 辞書データがない場合の警告ログ
        if (!$dictionaryData) {
            Log::info('Gemini API: Proceeding without dictionary data', [
                'word' => $word,
                'has_context' => !is_null($context)
            ]);
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

                if (!$generatedText) {
                    Log::error('Gemini API: Empty response', [
                        'word' => $word,
                        'response_body' => $response->body()
                    ]);
                    throw new \Exception('AIの応答が空でした。手動で入力してください');
                }

                // JSONを抽出してパース
                return $this->parseAIResponse($generatedText);
            } else {
                $statusCode = $response->status();
                $responseBody = $response->body();

                // レート制限エラー
                if ($statusCode === 429) {
                    Log::error('Gemini API: Rate limit exceeded', [
                        'word' => $word,
                        'status' => $statusCode
                    ]);
                    throw new \Exception('APIリクエスト数の上限に達しました。しばらく待ってから再度お試しください');
                }

                // 認証エラー
                if ($statusCode === 401 || $statusCode === 403) {
                    Log::error('Gemini API: Authentication failed', [
                        'word' => $word,
                        'status' => $statusCode
                    ]);
                    throw new \Exception('APIキーが無効です。設定を確認してください');
                }

                Log::error('Gemini API: Request failed', [
                    'word' => $word,
                    'status' => $statusCode,
                    'body' => $responseBody
                ]);
                throw new \Exception('AIへのリクエストが失敗しました。もう一度お試しください');
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Gemini API: Network error', [
                'word' => $word,
                'error' => $e->getMessage()
            ]);
            throw new \Exception('ネットワークエラーが発生しました。接続を確認してください');
        } catch (\Illuminate\Http\Client\RequestException $e) {
            if (strpos($e->getMessage(), 'timeout') !== false || strpos($e->getMessage(), 'timed out') !== false) {
                Log::error('Gemini API: Timeout', [
                    'word' => $word,
                    'error' => $e->getMessage()
                ]);
                throw new \Exception('APIリクエストがタイムアウトしました。もう一度お試しください');
            }
            Log::error('Gemini API: Request exception', [
                'word' => $word,
                'error' => $e->getMessage()
            ]);
            throw $e;
        } catch (\Exception $e) {
            // 既に処理済みのエラーメッセージはそのまま投げる
            if (strpos($e->getMessage(), 'API') !== false ||
                strpos($e->getMessage(), 'JSON') !== false ||
                strpos($e->getMessage(), 'パース') !== false ||
                strpos($e->getMessage(), '意味') !== false) {
                throw $e;
            }

            Log::error('Gemini API: Unexpected error', [
                'word' => $word,
                'error' => $e->getMessage(),
                'type' => get_class($e)
            ]);
            throw new \Exception('予期しないエラーが発生しました: ' . $e->getMessage());
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
            Log::error('AI response parsing failed: JSON block not found', [
                'response_text' => substr($text, 0, 500) // 最初の500文字のみログ
            ]);
            throw new \Exception('AIの応答を解析できませんでした。手動で入力してください');
        }

        $data = json_decode($jsonText, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('AI response parsing failed: JSON decode error', [
                'json_text' => substr($jsonText, 0, 500),
                'error' => json_last_error_msg()
            ]);
            throw new \Exception('AIの応答を解析できませんでした。手動で入力してください');
        }

        // 必須フィールドの確認
        if (!isset($data['meanings']) || !is_array($data['meanings'])) {
            Log::error('AI response parsing failed: Missing required field', [
                'data_keys' => array_keys($data)
            ]);
            throw new \Exception('意味(meanings)が見つかりませんでした。手動で入力してください');
        }

        if (empty($data['meanings'])) {
            Log::warning('AI response contains empty meanings array');
            throw new \Exception('意味が空でした。手動で入力してください');
        }

        Log::info('AI response parsed successfully', [
            'has_part_of_speech' => isset($data['part_of_speech']),
            'has_pronunciation' => isset($data['pronunciation_katakana']),
            'meanings_count' => count($data['meanings'])
        ]);

        return $data;
    }
}
