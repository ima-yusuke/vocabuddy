# 学習ノート機能 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** 英語学習のメモ（タイトル + 本文 + 画像複数枚）をブログ形式で作成・分類・管理できる「学習ノート」機能を追加する。

**Architecture:** notes / note_categories / note_images の3テーブルを user_id で所有者に紐付け、`NoteController`（リソース形式）+ Blade ビュー4枚（一覧・作成・詳細・編集）で CRUD を提供する。画像は public ディスク（`storage/app/public/note-images/{user_id}/`）に保存する。設計書: `docs/plans/2026-07-29-learning-notes-design.md`

**Tech Stack:** Laravel 11 / Blade / Tailwind CSS / PHPUnit（RefreshDatabase, Storage::fake）

**前提知識（このコードベースの流儀）:**
- 認証必須ページのルートは `routes/web.php` の `Route::middleware(['auth'])->group()` 内に追加する
- ビューは `<x-template title="...">` で包む（サイドメニュー・ナビ・フォント込み）。ページ背景は `bg-[#ffeb54]`、カードは `bg-white border-2 border-black rounded-2xl`。使用色は黄 `#ffeb54`・黒・白のみ（CLAUDE.md 参照）
- 所有権チェックは `auth()->user()->notes()->findOrFail($id)` 方式（他人のデータは自動的に404）
- テストは `tests/Feature/` に PHPUnit クラス、`use RefreshDatabase`
- テスト実行コマンド: `php artisan test --filter=NoteTest`

---

### Task 1: マイグレーション・モデル・ファクトリ

**Files:**
- Create: `database/migrations/2026_07_29_000001_create_note_categories_table.php`
- Create: `database/migrations/2026_07_29_000002_create_notes_table.php`
- Create: `database/migrations/2026_07_29_000003_create_note_images_table.php`
- Create: `app/Models/Note.php`
- Create: `app/Models/NoteCategory.php`
- Create: `app/Models/NoteImage.php`
- Create: `database/factories/NoteFactory.php`
- Create: `database/factories/NoteCategoryFactory.php`
- Modify: `app/Models/User.php`（relations 追加）
- Test: `tests/Feature/NoteModelTest.php`

- [x] **Step 1: 失敗するテストを書く**

`tests/Feature/NoteModelTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\Note;
use App\Models\NoteCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_note_belongs_to_user_and_category_and_has_images(): void
    {
        $user = User::factory()->create();
        $category = NoteCategory::factory()->create(['user_id' => $user->id, 'name' => '文法']);
        $note = Note::factory()->create([
            'user_id' => $user->id,
            'note_category_id' => $category->id,
            'title' => '現在分詞とは？',
        ]);
        $note->images()->create(['path' => 'note-images/1/a.png']);

        $this->assertSame($user->id, $note->user->id);
        $this->assertSame('文法', $note->category->name);
        $this->assertCount(1, $note->fresh()->images);
        $this->assertCount(1, $user->notes);
        $this->assertCount(1, $user->noteCategories);
    }

    public function test_category_is_set_null_on_category_delete(): void
    {
        $user = User::factory()->create();
        $category = NoteCategory::factory()->create(['user_id' => $user->id]);
        $note = Note::factory()->create([
            'user_id' => $user->id,
            'note_category_id' => $category->id,
        ]);

        $category->delete();

        $this->assertNull($note->fresh()->note_category_id);
    }
}
```

- [x] **Step 2: テストが失敗することを確認する**

Run: `php artisan test --filter=NoteModelTest`
Expected: FAIL（`note_categories` テーブルが存在しない / クラスが存在しない）

- [x] **Step 3: マイグレーション3本を作成する**

`database/migrations/2026_07_29_000001_create_note_categories_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('note_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name'); // カテゴリ名（例: 文法、イディオム）
            $table->timestamps();

            // 同一ユーザー内でカテゴリ名はユニーク
            $table->unique(['user_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('note_categories');
    }
};
```

`database/migrations/2026_07_29_000002_create_notes_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('note_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title'); // 例: 現在分詞とは？
            $table->text('body')->nullable(); // 本文テキスト
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
```

`database/migrations/2026_07_29_000003_create_note_images_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('note_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('note_id')->constrained()->onDelete('cascade');
            $table->string('path'); // publicディスク上の相対パス
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('note_images');
    }
};
```

- [x] **Step 4: モデル3つを作成する**

`app/Models/Note.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Note extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'note_category_id',
        'title',
        'body',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(NoteCategory::class, 'note_category_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(NoteImage::class);
    }
}
```

`app/Models/NoteCategory.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NoteCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }
}
```

`app/Models/NoteImage.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoteImage extends Model
{
    protected $fillable = [
        'note_id',
        'path',
    ];

    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }
}
```

- [x] **Step 5: ファクトリ2つを作成する**

`database/factories/NoteFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Note>
 */
class NoteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'note_category_id' => null,
            'title' => fake()->sentence(3),
            'body' => fake()->paragraph(),
        ];
    }
}
```

`database/factories/NoteCategoryFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NoteCategory>
 */
class NoteCategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->unique()->word(),
        ];
    }
}
```

- [x] **Step 6: User モデルに relations を追加する**

`app/Models/User.php` の `weakWords()` メソッドの直後に追加:

```php
    /**
     * このユーザーの学習ノート
     */
    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    /**
     * このユーザーのノートカテゴリ
     */
    public function noteCategories()
    {
        return $this->hasMany(NoteCategory::class);
    }
```

ファイル先頭の `use` 群に `use App\Models\Note;` `use App\Models\NoteCategory;` は不要（同一 namespace）。

- [x] **Step 7: テストが通ることを確認する**

Run: `php artisan test --filter=NoteModelTest`
Expected: PASS（2 tests）

- [x] **Step 8: 開発DBにマイグレーションを適用する**

Run: `php artisan migrate`
Expected: 3本のマイグレーションが `DONE`

- [x] **Step 9: コミット**

```bash
git add database/migrations app/Models database/factories tests/Feature/NoteModelTest.php
git commit -m "feat: 学習ノートのテーブル・モデル・ファクトリを追加"
```

---

### Task 2: ルーティングと一覧画面（index）

**Files:**
- Create: `app/Http/Controllers/NoteController.php`
- Create: `resources/views/notes/index.blade.php`
- Modify: `routes/web.php`（auth グループ内にルート追加）
- Test: `tests/Feature/NoteTest.php`

- [x] **Step 1: 失敗するテストを書く**

`tests/Feature/NoteTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\Note;
use App\Models\NoteCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/notes')->assertRedirect('/login');
    }

    public function test_index_displays_own_notes_only(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Note::factory()->create(['user_id' => $user->id, 'title' => '現在分詞とは？']);
        Note::factory()->create(['user_id' => $other->id, 'title' => '他人のメモ']);

        $response = $this->actingAs($user)->get('/notes');

        $response->assertOk();
        $response->assertSee('現在分詞とは？');
        $response->assertDontSee('他人のメモ');
    }

    public function test_index_can_be_filtered_by_category(): void
    {
        $user = User::factory()->create();
        $grammar = NoteCategory::factory()->create(['user_id' => $user->id, 'name' => '文法']);
        Note::factory()->create([
            'user_id' => $user->id,
            'note_category_id' => $grammar->id,
            'title' => '過去進行形とは？',
        ]);
        Note::factory()->create(['user_id' => $user->id, 'title' => 'カテゴリなしのメモ']);

        $response = $this->actingAs($user)->get('/notes?category=' . $grammar->id);

        $response->assertOk();
        $response->assertSee('過去進行形とは？');
        $response->assertDontSee('カテゴリなしのメモ');
    }
}
```

- [x] **Step 2: テストが失敗することを確認する**

Run: `php artisan test --filter=NoteTest`
Expected: FAIL（GET /notes が 404）

- [x] **Step 3: ルートを追加する**

`routes/web.php` の auth グループ内（`Route::get('/word/ai-quota', ...)` の直後）に追加し、ファイル先頭の use 群に `use App\Http\Controllers\NoteController;` を追加:

```php
    // 学習ノート
    Route::get('/notes', [NoteController::class, 'index'])->name('notes.index');
    Route::get('/notes/create', [NoteController::class, 'create'])->name('notes.create');
    Route::post('/notes', [NoteController::class, 'store'])->name('notes.store');
    Route::get('/notes/{id}', [NoteController::class, 'show'])->name('notes.show');
    Route::get('/notes/{id}/edit', [NoteController::class, 'edit'])->name('notes.edit');
    Route::patch('/notes/{id}', [NoteController::class, 'update'])->name('notes.update');
    Route::delete('/notes/{id}', [NoteController::class, 'destroy'])->name('notes.destroy');
```

注意: `/notes/create` は `/notes/{id}` より**前**に定義すること（後だと create が {id} に食われる）。

- [x] **Step 4: NoteController を作成する（この時点では index のみ）**

`app/Http/Controllers/NoteController.php`:

```php
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
```

- [x] **Step 5: 一覧ビューを作成する**

`resources/views/notes/index.blade.php`:

```blade
<x-template title="学習ノート">
    <div class="min-h-screen bg-[#ffeb54]">
        <section class="py-16 px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto">
                {{-- ヘッダー --}}
                <div class="flex items-center justify-between mb-10">
                    <h1 class="text-3xl font-bold text-black">学習ノート</h1>
                    <a href="{{ route('notes.create') }}"
                        class="bg-black text-[#ffeb54] px-6 py-3 rounded-xl font-semibold hover:bg-gray-800 transition-all duration-200">
                        ＋ 新しいメモ
                    </a>
                </div>

                {{-- フラッシュメッセージ --}}
                @if(session('success'))
                    <div class="bg-white border-2 border-black rounded-xl p-4 mb-6 font-semibold text-black">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- カテゴリ絞り込み --}}
                @if($categories->isNotEmpty())
                    <div class="flex flex-wrap gap-2 mb-8">
                        <a href="{{ route('notes.index') }}"
                            class="px-4 py-2 rounded-full border-2 border-black font-semibold transition-all duration-200 {{ !$categoryId ? 'bg-black text-[#ffeb54]' : 'bg-white text-black hover:bg-black hover:text-[#ffeb54]' }}">
                            すべて
                        </a>
                        @foreach($categories as $category)
                            <a href="{{ route('notes.index', ['category' => $category->id]) }}"
                                class="px-4 py-2 rounded-full border-2 border-black font-semibold transition-all duration-200 {{ (string)$categoryId === (string)$category->id ? 'bg-black text-[#ffeb54]' : 'bg-white text-black hover:bg-black hover:text-[#ffeb54]' }}">
                                {{ $category->name }}
                            </a>
                        @endforeach
                    </div>
                @endif

                {{-- メモ一覧 --}}
                @if($notes->isEmpty())
                    <div class="bg-white border-2 border-black rounded-2xl p-10 text-center">
                        <p class="text-black font-semibold mb-2">まだメモがありません</p>
                        <p class="text-gray-600 text-sm">「＋ 新しいメモ」から学んだことを記録しましょう</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($notes as $note)
                            <a href="{{ route('notes.show', $note->id) }}"
                                class="block bg-white border-2 border-black rounded-2xl p-6 hover:shadow-soft-lg hover:-translate-y-0.5 transition-all duration-200">
                                <div class="flex items-center justify-between mb-2">
                                    <h2 class="text-xl font-bold text-black">{{ $note->title }}</h2>
                                    @if($note->category)
                                        <span class="shrink-0 ml-4 px-3 py-1 rounded-full bg-[#ffeb54] border border-black text-sm font-semibold text-black">
                                            {{ $note->category->name }}
                                        </span>
                                    @endif
                                </div>
                                @if($note->body)
                                    <p class="text-gray-600 text-sm line-clamp-2">{{ $note->body }}</p>
                                @endif
                                <p class="text-gray-400 text-xs mt-3">{{ $note->updated_at->format('Y/m/d') }} 更新</p>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    </div>
</x-template>
```

- [x] **Step 6: テストが通ることを確認する**

Run: `php artisan test --filter=NoteTest`
Expected: PASS（3 tests）

- [x] **Step 7: コミット**

```bash
git add routes/web.php app/Http/Controllers/NoteController.php resources/views/notes tests/Feature/NoteTest.php
git commit -m "feat: 学習ノートの一覧画面とルーティングを追加"
```

---

### Task 3: 作成機能（create / store）

**Files:**
- Modify: `app/Http/Controllers/NoteController.php`（create / store / private ヘルパー追加）
- Create: `resources/views/notes/_form.blade.php`（作成・編集共通フォーム）
- Create: `resources/views/notes/create.blade.php`
- Test: `tests/Feature/NoteTest.php`（テスト追加）

- [x] **Step 1: 失敗するテストを書く**

`tests/Feature/NoteTest.php` のクラス内にテストを追加。ファイル先頭の use 群に以下を追加:

```php
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
```

追加するテスト:

```php
    public function test_note_can_be_created_with_new_category(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/notes', [
            'title' => '現在分詞とは？',
            'body' => '動詞のing形。形容詞のように名詞を修飾できる。',
            'new_category' => '文法',
        ]);

        $note = Note::where('user_id', $user->id)->first();
        $response->assertRedirect(route('notes.show', $note->id));
        $this->assertSame('現在分詞とは？', $note->title);
        $this->assertSame('文法', $note->category->name);
    }

    public function test_existing_category_is_reused_when_same_name_is_entered(): void
    {
        $user = User::factory()->create();
        NoteCategory::factory()->create(['user_id' => $user->id, 'name' => '文法']);

        $this->actingAs($user)->post('/notes', [
            'title' => '過去進行形とは？',
            'new_category' => '文法',
        ]);

        $this->assertSame(1, NoteCategory::where('user_id', $user->id)->count());
    }

    public function test_note_can_be_created_with_existing_category_selected(): void
    {
        $user = User::factory()->create();
        $category = NoteCategory::factory()->create(['user_id' => $user->id, 'name' => 'イディオム']);

        $this->actingAs($user)->post('/notes', [
            'title' => 'break the ice',
            'note_category_id' => $category->id,
        ]);

        $this->assertSame($category->id, Note::first()->note_category_id);
    }

    public function test_others_category_cannot_be_selected(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $othersCategory = NoteCategory::factory()->create(['user_id' => $other->id]);

        $response = $this->actingAs($user)->post('/notes', [
            'title' => 'テスト',
            'note_category_id' => $othersCategory->id,
        ]);

        $response->assertSessionHasErrors('note_category_id');
    }

    public function test_title_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/notes', ['title' => '']);

        $response->assertSessionHasErrors('title');
    }

    public function test_note_can_be_created_with_images(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)->post('/notes', [
            'title' => '画像つきメモ',
            'images' => [
                UploadedFile::fake()->image('grammar1.png'),
                UploadedFile::fake()->image('grammar2.jpg'),
            ],
        ]);

        $note = Note::first();
        $this->assertCount(2, $note->images);
        foreach ($note->images as $image) {
            Storage::disk('public')->assertExists($image->path);
            $this->assertStringStartsWith('note-images/' . $user->id . '/', $image->path);
        }
    }

    public function test_more_than_ten_images_are_rejected(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $images = [];
        for ($i = 0; $i < 11; $i++) {
            $images[] = UploadedFile::fake()->image("img{$i}.png");
        }

        $response = $this->actingAs($user)->post('/notes', [
            'title' => '画像多すぎメモ',
            'images' => $images,
        ]);

        $response->assertSessionHasErrors('images');
    }
```

注意: `UploadedFile::fake()->image()` は PHP の GD 拡張が必要。テスト実行時に GD 未導入エラーが出た場合は `php -m | grep gd` で確認し、導入してから進める。

- [x] **Step 2: テストが失敗することを確認する**

Run: `php artisan test --filter=NoteTest`
Expected: 追加分が FAIL（NoteController::create / store が存在しない）

- [x] **Step 3: NoteController に create / store とヘルパーを実装する**

`app/Http/Controllers/NoteController.php` を以下のように拡張（use 群に追加が必要）:

```php
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
```

クラス先頭に定数を追加:

```php
    private const MAX_IMAGES = 10;
```

メソッドを追加:

```php
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
```

- [x] **Step 4: 共通フォームと作成ビューを作成する**

`resources/views/notes/_form.blade.php`（`$note` は編集時のみ渡される。`$categories` は必須）:

```blade
{{-- エラーメッセージ --}}
@if($errors->any())
    <div class="bg-white border-2 border-black rounded-xl p-4 mb-6">
        <h3 class="text-black font-bold mb-2">入力エラー</h3>
        <ul class="list-disc list-inside text-black text-sm">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div>
    <label for="title" class="block text-sm font-semibold text-black mb-2">タイトル <span class="text-black">*</span></label>
    <input type="text" id="title" name="title" value="{{ old('title', $note->title ?? '') }}"
        placeholder="例: 現在分詞とは？"
        class="w-full border-2 border-black rounded-xl px-5 py-3 focus:outline-none focus:ring-4 focus:ring-[#ffeb54]/40 bg-white text-black placeholder-gray-400">
</div>

<div>
    <label for="note_category_id" class="block text-sm font-semibold text-black mb-2">カテゴリ</label>
    <select id="note_category_id" name="note_category_id"
        class="w-full border-2 border-black rounded-xl px-5 py-3 focus:outline-none focus:ring-4 focus:ring-[#ffeb54]/40 bg-white text-black">
        <option value="">カテゴリなし</option>
        @foreach($categories as $category)
            <option value="{{ $category->id }}"
                @selected((string) old('note_category_id', $note->note_category_id ?? '') === (string) $category->id)>
                {{ $category->name }}
            </option>
        @endforeach
    </select>
    <input type="text" name="new_category" value="{{ old('new_category') }}"
        placeholder="または新しいカテゴリ名を入力（例: 文法）"
        class="mt-2 w-full border-2 border-black rounded-xl px-5 py-3 focus:outline-none focus:ring-4 focus:ring-[#ffeb54]/40 bg-white text-black placeholder-gray-400">
    <p class="text-xs text-gray-600 mt-1">新しいカテゴリ名を入力した場合はそちらが優先されます</p>
</div>

<div>
    <label for="body" class="block text-sm font-semibold text-black mb-2">本文</label>
    <textarea id="body" name="body" rows="10"
        placeholder="学んだことを自由にメモしましょう"
        class="w-full border-2 border-black rounded-xl px-5 py-3 focus:outline-none focus:ring-4 focus:ring-[#ffeb54]/40 bg-white text-black placeholder-gray-400 resize-y">{{ old('body', $note->body ?? '') }}</textarea>
</div>

{{-- 既存画像（編集時のみ） --}}
@if(isset($note) && $note->images->isNotEmpty())
    <div>
        <p class="block text-sm font-semibold text-black mb-2">アップロード済みの画像（チェックで削除）</p>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
            @foreach($note->images as $image)
                <label class="block cursor-pointer">
                    <img src="{{ asset('storage/' . $image->path) }}" alt=""
                        class="w-full h-32 object-cover border-2 border-black rounded-xl">
                    <span class="flex items-center mt-1 text-sm text-black">
                        <input type="checkbox" name="delete_image_ids[]" value="{{ $image->id }}"
                            class="mr-2 border-2 border-black rounded">
                        削除する
                    </span>
                </label>
            @endforeach
        </div>
    </div>
@endif

<div>
    <label for="images" class="block text-sm font-semibold text-black mb-2">画像を追加（複数選択可・最大10枚・1枚5MBまで）</label>
    <input type="file" id="images" name="images[]" multiple accept=".jpg,.jpeg,.png,.webp,.gif"
        class="w-full border-2 border-black rounded-xl px-5 py-3 bg-white text-black file:mr-4 file:py-1 file:px-4 file:rounded-full file:border-2 file:border-black file:bg-[#ffeb54] file:text-black file:font-semibold">
</div>
```

`resources/views/notes/create.blade.php`:

```blade
<x-template title="メモを作成">
    <div class="min-h-screen bg-[#ffeb54]">
        <section class="py-16 px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto">
                <a href="{{ route('notes.index') }}" class="inline-block mb-6 text-black font-semibold hover:underline">← 一覧に戻る</a>
                <div class="bg-white border-2 border-black rounded-2xl p-10">
                    <h1 class="text-2xl font-bold text-black mb-8">新しいメモ</h1>
                    <form method="post" action="{{ route('notes.store') }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        @include('notes._form')
                        <button type="submit"
                            class="w-full bg-black text-[#ffeb54] px-6 py-3 rounded-xl font-semibold hover:bg-gray-800 transition-all duration-200">
                            保存する
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </div>
</x-template>
```

- [x] **Step 5: テストが通ることを確認する**

Run: `php artisan test --filter=NoteTest`
Expected: PASS（Task 2 の3件 + 追加7件 = 10 tests）

※ この時点で store のリダイレクト先 `notes.show` のルートは定義済み（Task 2）だが show メソッドは未実装。`assertRedirect` はリダイレクト先を実際には呼ばないため問題ない。

- [x] **Step 6: コミット**

```bash
git add app/Http/Controllers/NoteController.php resources/views/notes tests/Feature/NoteTest.php
git commit -m "feat: 学習ノートの作成機能を追加（カテゴリ・画像対応）"
```

---

### Task 4: 詳細画面（show）

**Files:**
- Modify: `app/Http/Controllers/NoteController.php`（show 追加）
- Create: `resources/views/notes/show.blade.php`
- Test: `tests/Feature/NoteTest.php`（テスト追加）

- [x] **Step 1: 失敗するテストを書く**

`tests/Feature/NoteTest.php` に追加:

```php
    public function test_note_detail_is_displayed(): void
    {
        $user = User::factory()->create();
        $note = Note::factory()->create([
            'user_id' => $user->id,
            'title' => '現在分詞とは？',
            'body' => '動詞のing形。',
        ]);
        $note->images()->create(['path' => 'note-images/' . $user->id . '/sample.png']);

        $response = $this->actingAs($user)->get('/notes/' . $note->id);

        $response->assertOk();
        $response->assertSee('現在分詞とは？');
        $response->assertSee('動詞のing形。');
        $response->assertSee('note-images/' . $user->id . '/sample.png');
    }

    public function test_others_note_returns_404(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $note = Note::factory()->create(['user_id' => $other->id]);

        $this->actingAs($user)->get('/notes/' . $note->id)->assertNotFound();
    }
```

- [x] **Step 2: テストが失敗することを確認する**

Run: `php artisan test --filter=NoteTest`
Expected: 追加2件が FAIL（show メソッドが存在しない）

- [x] **Step 3: show メソッドを実装する**

`app/Http/Controllers/NoteController.php` に追加:

```php
    public function show(string $id)
    {
        $note = auth()->user()->notes()->with(['category', 'images'])->findOrFail($id);
        return view('notes.show', compact('note'));
    }
```

- [x] **Step 4: 詳細ビューを作成する**

`resources/views/notes/show.blade.php`:

```blade
<x-template :title="$note->title">
    <div class="min-h-screen bg-[#ffeb54]">
        <section class="py-16 px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto">
                <a href="{{ route('notes.index') }}" class="inline-block mb-6 text-black font-semibold hover:underline">← 一覧に戻る</a>

                @if(session('success'))
                    <div class="bg-white border-2 border-black rounded-xl p-4 mb-6 font-semibold text-black">
                        {{ session('success') }}
                    </div>
                @endif

                <article class="bg-white border-2 border-black rounded-2xl p-10">
                    <div class="flex items-start justify-between mb-6">
                        <div>
                            <h1 class="text-3xl font-bold text-black mb-2">{{ $note->title }}</h1>
                            <div class="flex items-center gap-3">
                                @if($note->category)
                                    <span class="px-3 py-1 rounded-full bg-[#ffeb54] border border-black text-sm font-semibold text-black">
                                        {{ $note->category->name }}
                                    </span>
                                @endif
                                <span class="text-gray-400 text-sm">{{ $note->updated_at->format('Y/m/d') }} 更新</span>
                            </div>
                        </div>
                        <a href="{{ route('notes.edit', $note->id) }}"
                            class="shrink-0 bg-black text-[#ffeb54] px-5 py-2 rounded-xl font-semibold hover:bg-gray-800 transition-all duration-200">
                            編集
                        </a>
                    </div>

                    @if($note->body)
                        <div class="text-black leading-relaxed whitespace-pre-wrap mb-8">{{ $note->body }}</div>
                    @endif

                    @if($note->images->isNotEmpty())
                        <div class="space-y-6">
                            @foreach($note->images as $image)
                                <img src="{{ asset('storage/' . $image->path) }}" alt=""
                                    class="w-full border-2 border-black rounded-2xl">
                            @endforeach
                        </div>
                    @endif
                </article>
            </div>
        </section>
    </div>
</x-template>
```

- [x] **Step 5: テストが通ることを確認する**

Run: `php artisan test --filter=NoteTest`
Expected: PASS（12 tests）

- [x] **Step 6: コミット**

```bash
git add app/Http/Controllers/NoteController.php resources/views/notes/show.blade.php tests/Feature/NoteTest.php
git commit -m "feat: 学習ノートの詳細画面を追加"
```

---

### Task 5: 編集機能（edit / update）

**Files:**
- Modify: `app/Http/Controllers/NoteController.php`（edit / update 追加）
- Create: `resources/views/notes/edit.blade.php`
- Test: `tests/Feature/NoteTest.php`（テスト追加）

- [x] **Step 1: 失敗するテストを書く**

`tests/Feature/NoteTest.php` に追加:

```php
    public function test_note_can_be_updated(): void
    {
        $user = User::factory()->create();
        $note = Note::factory()->create(['user_id' => $user->id, 'title' => '旧タイトル']);

        $response = $this->actingAs($user)->patch('/notes/' . $note->id, [
            'title' => '新タイトル',
            'body' => '更新後の本文',
            'new_category' => '文法',
        ]);

        $response->assertRedirect(route('notes.show', $note->id));
        $note->refresh();
        $this->assertSame('新タイトル', $note->title);
        $this->assertSame('文法', $note->category->name);
    }

    public function test_others_note_cannot_be_updated(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $note = Note::factory()->create(['user_id' => $other->id, 'title' => '他人のメモ']);

        $this->actingAs($user)
            ->patch('/notes/' . $note->id, ['title' => '乗っ取り'])
            ->assertNotFound();

        $this->assertSame('他人のメモ', $note->fresh()->title);
    }

    public function test_existing_image_can_be_deleted_on_update(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)->post('/notes', [
            'title' => '画像つきメモ',
            'images' => [UploadedFile::fake()->image('a.png')],
        ]);
        $note = Note::first();
        $image = $note->images->first();

        $this->actingAs($user)->patch('/notes/' . $note->id, [
            'title' => '画像つきメモ',
            'delete_image_ids' => [$image->id],
        ]);

        $this->assertCount(0, $note->fresh()->images);
        Storage::disk('public')->assertMissing($image->path);
    }

    public function test_image_can_be_added_on_update(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $note = Note::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->patch('/notes/' . $note->id, [
            'title' => $note->title,
            'images' => [UploadedFile::fake()->image('added.png')],
        ]);

        $this->assertCount(1, $note->fresh()->images);
    }

    public function test_total_images_cannot_exceed_ten_on_update(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $note = Note::factory()->create(['user_id' => $user->id]);
        for ($i = 0; $i < 9; $i++) {
            $note->images()->create(['path' => "note-images/{$user->id}/img{$i}.png"]);
        }

        $response = $this->actingAs($user)->patch('/notes/' . $note->id, [
            'title' => $note->title,
            'images' => [
                UploadedFile::fake()->image('x.png'),
                UploadedFile::fake()->image('y.png'),
            ],
        ]);

        $response->assertSessionHasErrors('images');
        $this->assertCount(9, $note->fresh()->images);
    }

    public function test_edit_form_is_displayed(): void
    {
        $user = User::factory()->create();
        $note = Note::factory()->create(['user_id' => $user->id, 'title' => '編集対象メモ']);

        $response = $this->actingAs($user)->get('/notes/' . $note->id . '/edit');

        $response->assertOk();
        $response->assertSee('編集対象メモ');
    }
```

- [x] **Step 2: テストが失敗することを確認する**

Run: `php artisan test --filter=NoteTest`
Expected: 追加6件が FAIL（edit / update メソッドが存在しない）

- [x] **Step 3: edit / update メソッドを実装する**

`app/Http/Controllers/NoteController.php` に追加:

```php
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
```

- [x] **Step 4: 編集ビューを作成する**

`resources/views/notes/edit.blade.php`:

```blade
<x-template title="メモを編集">
    <div class="min-h-screen bg-[#ffeb54]">
        <section class="py-16 px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto">
                <a href="{{ route('notes.show', $note->id) }}" class="inline-block mb-6 text-black font-semibold hover:underline">← メモに戻る</a>
                <div class="bg-white border-2 border-black rounded-2xl p-10">
                    <h1 class="text-2xl font-bold text-black mb-8">メモを編集</h1>
                    <form method="post" action="{{ route('notes.update', $note->id) }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        @method('PATCH')
                        @include('notes._form')
                        <button type="submit"
                            class="w-full bg-black text-[#ffeb54] px-6 py-3 rounded-xl font-semibold hover:bg-gray-800 transition-all duration-200">
                            更新する
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </div>
</x-template>
```

- [x] **Step 5: テストが通ることを確認する**

Run: `php artisan test --filter=NoteTest`
Expected: PASS（18 tests）

- [x] **Step 6: コミット**

```bash
git add app/Http/Controllers/NoteController.php resources/views/notes/edit.blade.php tests/Feature/NoteTest.php
git commit -m "feat: 学習ノートの編集機能を追加（画像の個別削除・追加対応）"
```

---

### Task 6: 削除機能（destroy）

**Files:**
- Modify: `app/Http/Controllers/NoteController.php`（destroy 追加）
- Modify: `resources/views/notes/show.blade.php`（削除ボタン追加）
- Test: `tests/Feature/NoteTest.php`（テスト追加）

- [x] **Step 1: 失敗するテストを書く**

`tests/Feature/NoteTest.php` に追加:

```php
    public function test_note_can_be_deleted_with_its_images(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)->post('/notes', [
            'title' => '削除対象メモ',
            'images' => [UploadedFile::fake()->image('a.png')],
        ]);
        $note = Note::first();
        $imagePath = $note->images->first()->path;

        $response = $this->actingAs($user)->delete('/notes/' . $note->id);

        $response->assertRedirect(route('notes.index'));
        $this->assertNull(Note::find($note->id));
        Storage::disk('public')->assertMissing($imagePath);
    }

    public function test_others_note_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $note = Note::factory()->create(['user_id' => $other->id]);

        $this->actingAs($user)->delete('/notes/' . $note->id)->assertNotFound();
        $this->assertNotNull(Note::find($note->id));
    }
```

- [x] **Step 2: テストが失敗することを確認する**

Run: `php artisan test --filter=NoteTest`
Expected: 追加2件が FAIL（destroy メソッドが存在しない）

- [x] **Step 3: destroy メソッドを実装する**

`app/Http/Controllers/NoteController.php` に追加:

```php
    public function destroy(string $id)
    {
        $note = auth()->user()->notes()->with('images')->findOrFail($id);

        foreach ($note->images as $image) {
            Storage::disk('public')->delete($image->path);
        }

        $note->delete();

        return redirect()->route('notes.index')->with('success', 'メモを削除しました');
    }
```

- [x] **Step 4: 詳細画面に削除ボタンを追加する**

`resources/views/notes/show.blade.php` の編集ボタン（`<a href="{{ route('notes.edit', ...`）の直後に追加。既存の `<a>` を `<div class="shrink-0 flex items-center gap-2">` で包んで並べる:

```blade
                        <div class="shrink-0 flex items-center gap-2">
                            <a href="{{ route('notes.edit', $note->id) }}"
                                class="bg-black text-[#ffeb54] px-5 py-2 rounded-xl font-semibold hover:bg-gray-800 transition-all duration-200">
                                編集
                            </a>
                            <form method="post" action="{{ route('notes.destroy', $note->id) }}"
                                onsubmit="return confirm('このメモを削除しますか？画像も一緒に削除されます。');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="bg-white text-black border-2 border-black px-5 py-2 rounded-xl font-semibold hover:bg-black hover:text-[#ffeb54] transition-all duration-200">
                                    削除
                                </button>
                            </form>
                        </div>
```

- [x] **Step 5: テストが通ることを確認する**

Run: `php artisan test --filter=NoteTest`
Expected: PASS（20 tests）

- [x] **Step 6: コミット**

```bash
git add app/Http/Controllers/NoteController.php resources/views/notes/show.blade.php tests/Feature/NoteTest.php
git commit -m "feat: 学習ノートの削除機能を追加"
```

---

### Task 7: サイドメニューへのリンク追加と仕上げ

**Files:**
- Modify: `resources/views/components/side-menu.blade.php`（学習ノートのリンク追加）
- Test: 既存の全テスト

- [x] **Step 1: サイドメニューにリンクを追加する**

`resources/views/components/side-menu.blade.php` の認証済みユーザー向けセクション内、「単語テスト」の `</li>` の直後に追加（既存リンクと同じスタイル）:

```blade
                    <li class="side_li">
                        <a href="{{ route('notes.index') }}" class="flex items-center px-4 py-3 text-white hover:bg-white/10 rounded-xl transition-all duration-300 group">
                            <svg class="w-5 h-5 mr-3 text-[#ffeb54] group-hover:text-[#ffeb54]/80 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            <span class="text-lg font-semibold">学習ノート</span>
                        </a>
                    </li>
```

- [x] **Step 2: 全テストを実行してリグレッションがないことを確認する**

Run: `php artisan test`
Expected: 全テスト PASS（既存テスト含む）

- [x] **Step 3: storage リンクを確認する**

Run: `php artisan storage:link`
Expected: すでにリンク済みなら「The [public/storage] link already exists」、なければ作成される。どちらでもOK。

- [x] **Step 4: 手動確認**

開発サーバーで以下を確認する（`docker-compose.yml` がある環境なら `docker compose up -d` 済み前提。起動方法が不明なら README.md を参照）:

1. ログイン後、サイドメニューに「学習ノート」が表示される
2. `/notes` → 空状態の表示
3. 新しいメモを画像つき・新規カテゴリ「文法」で作成 → 詳細画面に画像が表示される
4. 一覧でカテゴリ「文法」の絞り込みチップが機能する
5. 編集で画像を削除・追加できる
6. 削除で一覧に戻り、メモが消えている

- [x] **Step 5: コミット**

```bash
git add resources/views/components/side-menu.blade.php
git commit -m "feat: サイドメニューに学習ノートのリンクを追加"
```
