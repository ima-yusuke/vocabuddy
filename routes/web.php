<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MainController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\ReplyController;
use App\Http\Controllers\WordAutoCompleteController;


Route::get('/', [MainController::class, 'ShowIndex'])->name('ShowIndex');
//Route::post('/', [MainController::class, 'AddProduct'])->name('AddProduct');
Route::post('/update/{id}', [MainController::class, 'CheckProduct'])->name('CheckProduct');

Route::post('/', [MainController::class, 'AddWord'])->name('AddWord');
Route::get('/word/edit/{id}', [MainController::class, 'EditWord'])->name('EditWord');
Route::patch('/word/update/{id}', [MainController::class, 'UpdateWord'])->name('UpdateWord');
Route::delete('/', [MainController::class, 'DeleteWord'])->name('DeleteWord');

Route::get('/test', [TestController::class, 'ShowTestStart'])->name('ShowTest');
Route::get('/test/start', [TestController::class, 'StartTest'])->name('StartTest');
Route::get('/test/question', [TestController::class, 'ShowQuestion'])->name('ShowQuestion');
Route::post('/test/check', [TestController::class, 'CheckAnswer'])->name('CheckAnswer');

Route::get('/reply-assistant', [ReplyController::class, 'ShowReplyAssistant'])->name('ShowReplyAssistant');
Route::post('/reply-assistant/generate', [ReplyController::class, 'GenerateReply'])->name('GenerateReply');

Route::post('/word/autocomplete', [WordAutoCompleteController::class, 'autocomplete'])->name('AutocompleteWord');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
