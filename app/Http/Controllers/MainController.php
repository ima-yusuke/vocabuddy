<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Word;
use App\Models\Japanese;

class MainController extends Controller
{

    public function ShowIndex(Request $request){
        $search = $request->input('search');

        if ($search) {
            // 英単語または日本語の意味で検索
            $words = Word::with('japanese')
                ->where('word', 'like', '%' . $search . '%')
                ->orWhere('en_example', 'like', '%' . $search . '%')
                ->orWhere('jp_example', 'like', '%' . $search . '%')
                ->orWhereHas('japanese', function($query) use ($search) {
                    $query->where('japanese', 'like', '%' . $search . '%');
                })
                ->get();

            // 総単語数を取得
            $totalCount = Word::count();
        } else {
            $words = Word::with('japanese')->get();
            $totalCount = $words->count();
        }

        return view("index", compact('words', 'totalCount'));
    }

    public function AddWord(Request $request){

        // バリデーション
        $validated = $request->validate([
            'word' => 'required|string|max:255',
            'en_example' => 'nullable|string',
            'jp_example' => 'nullable|string',
            'part_of_speech' => 'nullable|string|max:50',
            'pronunciation' => 'nullable|string|max:255',
            'pronunciation_katakana' => 'nullable|string|max:255',
            'meaningArray' => 'required|array',
            'meaningArray.*' => 'required|string',
        ]);

        // フォームから送信された意味を配列として取得
        $meanings = $request->input('meaningArray');

        // データベースにデータを保存する
        $word = new Word();
        $word->word = $request->word;
        $word->en_example = $request->en_example;
        $word->jp_example = $request->jp_example;
        $word->part_of_speech = $request->part_of_speech;
        $word->pronunciation = $request->pronunciation;
        $word->pronunciation_katakana = $request->pronunciation_katakana;
        $word->save();

        // Japaneseの保存
        for ($i = 0; $i < count($meanings); $i++) {
            $japanese = new Japanese();
            $japanese->word_id = $word->id;
            $japanese->japanese = $meanings[$i];
            $japanese->save();
        }


        return redirect()->back();
    }

    public function DeleteWord(Request $request)
    {
        $id = $request->id;
        $word = Word::findOrFail($id); // 単語が見つからない場合は404エラー
        $word->delete();
        return redirect()->back();
    }

    public function AddProduct(Request $request){
        // データベースにデータを保存する
        $product = new Product();
        $product->name = $request->p_name;
        $product->price = $request->price;
        $product->quantity = $request->quantity;
        $product->date = $request->date;
        $product->save();

        return redirect()->back();
    }

    public function CheckProduct($id)
    {
        $product = Product::findOrFail($id); // 商品が見つからない場合は404エラー
        $product->flag = !$product->flag;
        $product->save();
        return redirect()->back();
    }

    public function DeleteProduct($id)
    {
        $product = Product::findOrFail($id); // 商品が見つからない場合は404エラー
        $product->delete();
        return redirect()->back();
    }
}
