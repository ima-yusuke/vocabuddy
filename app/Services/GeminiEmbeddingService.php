<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeminiEmbeddingService
{
    private string $apiKey;
    private const MODEL = 'gemini-embedding-001';
    private const API_ENDPOINT = 'https://generativelanguage.googleapis.com/v1beta/models/';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
    }

    /**
     * テキストからEmbeddingベクトルを生成
     *
     * @param string $text
     * @return array|null
     */
    public function generateEmbedding(string $text): ?array
    {
        \Log::info('=== Embedding Generation START ===', [
            'text_length' => strlen($text),
            'text_preview' => substr($text, 0, 100),
            'has_api_key' => !empty($this->apiKey)
        ]);

        if (empty($this->apiKey)) {
            \Log::error('Embedding generation failed: API key is empty');
            throw new \Exception('Gemini API key is not configured');
        }

        try {
            $url = self::API_ENDPOINT . self::MODEL . ':embedContent?key=' . $this->apiKey;
            \Log::info('Calling Gemini Embedding API', [
                'endpoint' => self::API_ENDPOINT . self::MODEL . ':embedContent',
                'model' => 'models/' . self::MODEL
            ]);

            $response = Http::timeout(30)->post(
                $url,
                [
                    'model' => 'models/' . self::MODEL,
                    'content' => [
                        'parts' => [
                            ['text' => $text]
                        ]
                    ]
                ]
            );

            \Log::info('API Response received', [
                'status' => $response->status(),
                'successful' => $response->successful()
            ]);

            if ($response->successful()) {
                $result = $response->json();
                $embedding = $result['embedding']['values'] ?? null;

                \Log::info('Embedding extracted', [
                    'has_embedding' => !is_null($embedding),
                    'vector_length' => $embedding ? count($embedding) : 0
                ]);

                return $embedding;
            }

            \Log::error('Embedding API request failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            \Log::error('Embedding generation exception: ' . $e->getMessage(), [
                'exception_class' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * コサイン類似度を計算
     *
     * @param array $vector1
     * @param array $vector2
     * @return float
     */
    public function cosineSimilarity(array $vector1, array $vector2): float
    {
        if (count($vector1) !== count($vector2)) {
            throw new \InvalidArgumentException('Vectors must have the same dimension');
        }

        $dotProduct = 0;
        $magnitude1 = 0;
        $magnitude2 = 0;

        for ($i = 0; $i < count($vector1); $i++) {
            $dotProduct += $vector1[$i] * $vector2[$i];
            $magnitude1 += $vector1[$i] * $vector1[$i];
            $magnitude2 += $vector2[$i] * $vector2[$i];
        }

        $magnitude1 = sqrt($magnitude1);
        $magnitude2 = sqrt($magnitude2);

        if ($magnitude1 == 0 || $magnitude2 == 0) {
            return 0;
        }

        return $dotProduct / ($magnitude1 * $magnitude2);
    }

    /**
     * 返信文の検索クエリ用テキストを生成
     * intentJaを3回繰り返すことで、返信意図により重点を置く
     *
     * @param string $partnerMessage
     * @param string $intentJa
     * @return string
     */
    public function createSearchQuery(string $partnerMessage, string $intentJa): string
    {
        // 返信意図を3回繰り返すことで、embeddingでの重みを高める
        return $partnerMessage . ' ' . $intentJa . ' ' . $intentJa . ' ' . $intentJa;
    }
}
