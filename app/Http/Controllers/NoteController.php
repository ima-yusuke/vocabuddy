<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\NoteCategory;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();
        $categoryId = $request->input('category');

        $query = Note::with('category')
            ->where('user_id', $userId)
            ->latest('updated_at');

        if ($categoryId) {
            $query->where('note_category_id', $categoryId);
        }

        $notes = $query->get();
        $categories = NoteCategory::where('user_id', $userId)->orderBy('name')->get();

        return view('notes.index', compact('notes', 'categories', 'categoryId'));
    }
}
