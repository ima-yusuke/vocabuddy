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
        // 次のステップで実装
        return null;
    }

    /**
     * Gemini APIで整形
     */
    private function formatWithAI(string $word, ?string $context, ?array $dictionaryData): array
    {
        // 次のステップで実装
        return [];
    }
}
