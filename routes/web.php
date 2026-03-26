<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MainController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\ReplyController;
use App\Http\Controllers\WordAutoCompleteController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\PricingController;

/*
|--------------------------------------------------------------------------
| Phase 1: ランディングページ + ルーティング変更
|--------------------------------------------------------------------------
| 旧ルートのバックアップ:
| Route::get('/', [MainController::class, 'ShowIndex'])->name('ShowIndex');
| Route::post('/', [MainController::class, 'AddWord'])->name('AddWord');
| Route::delete('/', [MainController::class, 'DeleteWord'])->name('DeleteWord');
*/

// ランディングページ（全ユーザー）
Route::get('/', [LandingController::class, 'index'])->name('landing');

// プラン・価格ページ（全ユーザー）
Route::get('/pricing', [PricingController::class, 'index'])->name('pricing');

// 単語一覧・管理（認証必須）
Route::middleware(['auth'])->group(function () {
    Route::get('/words', [MainController::class, 'ShowIndex'])->name('words.index');
    Route::post('/words', [MainController::class, 'AddWord'])->name('words.store');
    Route::get('/words/edit/{id}', [MainController::class, 'EditWord'])->name('words.edit');
    Route::patch('/words/update/{id}', [MainController::class, 'UpdateWord'])->name('words.update');
    Route::delete('/words', [MainController::class, 'DeleteWord'])->name('words.destroy');

    // 単語テスト
    Route::get('/test', [TestController::class, 'ShowTestStart'])->name('ShowTest');
    Route::get('/test/start', [TestController::class, 'StartTest'])->name('StartTest');
    Route::get('/test/question', [TestController::class, 'ShowQuestion'])->name('ShowQuestion');
    Route::post('/test/check', [TestController::class, 'CheckAnswer'])->name('CheckAnswer');

    // 返信アシスタント
    Route::get('/reply-assistant', [ReplyController::class, 'ShowReplyAssistant'])->name('ShowReplyAssistant');
    Route::get('/reply-history', [ReplyController::class, 'ShowHistory'])->name('ShowReplyHistory');
    Route::post('/reply-assistant/find-similar', [ReplyController::class, 'FindSimilarReplies'])->name('FindSimilarReplies');
    Route::post('/reply-assistant/generate', [ReplyController::class, 'GenerateReply'])->name('GenerateReply');

    // AI自動補完
    Route::post('/word/autocomplete', [WordAutoCompleteController::class, 'autocomplete'])
        ->middleware('check.ai.usage:autocomplete')
        ->name('AutocompleteWord');
});

// ダッシュボード（認証必須）
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// マイページ（認証必須）
Route::middleware('auth')->group(function () {
    Route::get('/mypage', [ProfileController::class, 'edit'])->name('mypage');
    Route::patch('/mypage', [ProfileController::class, 'update'])->name('mypage.update');
    Route::delete('/mypage', [ProfileController::class, 'destroy'])->name('mypage.destroy');
});

require __DIR__.'/auth.php';
