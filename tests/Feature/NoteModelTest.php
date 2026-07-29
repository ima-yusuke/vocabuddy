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
