<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\NoteCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class NoteController extends Controller
{
    private const MAX_IMAGES = 10;

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

    public function create()
    {
        $categories = NoteCategory::where('user_id', auth()->id())->orderBy('name')->get();
        return view('notes.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateNote($request);

        $note = Note::create([
            'user_id' => auth()->id(),
            'note_category_id' => $this->resolveCategoryId($request),
            'title' => $validated['title'],
            'body' => $validated['body'] ?? null,
        ]);

        $this->storeImages($request, $note);

        return redirect()->route('notes.show', $note->id)->with('success', 'メモを作成しました');
    }

    public function show(string $id)
    {
        $note = auth()->user()->notes()->with(['category', 'images'])->findOrFail($id);
        return view('notes.show', compact('note'));
    }

    public function edit(string $id)
    {
        $note = auth()->user()->notes()->with('images')->findOrFail($id);
        $categories = NoteCategory::where('user_id', auth()->id())->orderBy('name')->get();
        return view('notes.edit', compact('note', 'categories'));
    }

    public function update(Request $request, string $id)
    {
        $note = auth()->user()->notes()->with('images')->findOrFail($id);
        $validated = $this->validateNote($request);

        // 画像の合計枚数チェック（既存 - 削除 + 新規 <= 10）
        $deleteIds = collect($request->input('delete_image_ids', []))->map(fn ($v) => (int) $v);
        $newCount = count($request->file('images', []));
        $totalAfter = $note->images->whereNotIn('id', $deleteIds)->count() + $newCount;
        if ($totalAfter > self::MAX_IMAGES) {
            throw ValidationException::withMessages([
                'images' => '画像は1メモにつき最大' . self::MAX_IMAGES . '枚までです',
            ]);
        }

        $note->update([
            'note_category_id' => $this->resolveCategoryId($request),
            'title' => $validated['title'],
            'body' => $validated['body'] ?? null,
        ]);

        // チェックされた既存画像を削除（storage上のファイルも消す）
        foreach ($note->images->whereIn('id', $deleteIds) as $image) {
            Storage::disk('public')->delete($image->path);
            $image->delete();
        }

        $this->storeImages($request, $note);

        return redirect()->route('notes.show', $note->id)->with('success', 'メモを更新しました');
    }

    /**
     * 作成・更新共通のバリデーション
     */
    private function validateNote(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'nullable|string',
            'note_category_id' => [
                'nullable',
                Rule::exists('note_categories', 'id')->where('user_id', auth()->id()),
            ],
            'new_category' => 'nullable|string|max:50',
            'images' => 'nullable|array|max:' . self::MAX_IMAGES,
            'images.*' => 'image|mimes:jpg,jpeg,png,webp,gif|max:5120',
            'delete_image_ids' => 'nullable|array',
            'delete_image_ids.*' => 'integer',
        ], [
            'title.required' => 'タイトルを入力してください',
            'images.max' => '画像は1メモにつき最大' . self::MAX_IMAGES . '枚までです',
            'images.*.image' => '画像ファイルを選択してください',
            'images.*.mimes' => '画像は jpg / png / webp / gif 形式のみ対応しています',
            'images.*.max' => '画像は1枚5MBまでです',
        ]);
    }

    /**
     * カテゴリIDを解決する（新規名の入力があれば作成 or 同名を再利用）
     */
    private function resolveCategoryId(Request $request): ?int
    {
        $newName = trim((string) $request->input('new_category'));

        if ($newName !== '') {
            return NoteCategory::firstOrCreate([
                'user_id' => auth()->id(),
                'name' => $newName,
            ])->id;
        }

        $selected = $request->input('note_category_id');

        return $selected ? (int) $selected : null;
    }

    /**
     * アップロードされた画像を保存する
     */
    private function storeImages(Request $request, Note $note): void
    {
        foreach ($request->file('images', []) as $file) {
            $path = $file->store('note-images/' . auth()->id(), 'public');
            $note->images()->create(['path' => $path]);
        }
    }
}
