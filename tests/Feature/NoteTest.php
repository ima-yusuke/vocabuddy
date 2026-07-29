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
