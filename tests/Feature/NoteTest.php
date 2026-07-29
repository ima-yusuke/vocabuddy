<?php

namespace Tests\Feature;

use App\Models\Note;
use App\Models\NoteCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
}
