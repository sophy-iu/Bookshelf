<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    public function test_ジャンル一覧画面を表示できる(): void
    {
        $user = User::factory()->create();

        Genre::factory()->create([
            'name' => '小説',
        ]);

        Genre::factory()->create([
            'name' => '技術書',
        ]);

        $response = $this->actingAs($user)->get('/genres');

        $response->assertStatus(200);

        $response->assertSee('小説');
        $response->assertSee('技術書');
    }
    
    public function test_ジャンル登録画面を表示できる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/genres/create');

        $response->assertStatus(200);
    }

    public function test_ジャンルを登録できる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/genres', [
                'name' => 'ミステリー',
            ]);

        $response->assertSessionHasNoErrors();

        $response->assertRedirect(route('genres.index'));

        $this->assertDatabaseHas('genres', [
            'name' => 'ミステリー',
        ]);
    }

    public function test_ジャンル名が空の場合は登録できない(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/genres', [
                'name' => '',
            ]);

        $response->assertSessionHasErrors([
            'name',
        ]);

        $this->assertDatabaseCount('genres', 0);
    }

    public function test_ジャンル編集画面を表示できる(): void
    {
        $user = User::factory()->create();

        $genre = Genre::factory()->create([
            'name' => '小説',
        ]);

        $response = $this->actingAs($user)
            ->get("/genres/{$genre->id}/edit");

        $response->assertStatus(200);
        $response->assertSee('小説');
    }

    public function test_ジャンルを更新できる(): void
    {
        $user = User::factory()->create();

        $genre = Genre::factory()->create([
            'name' => '小説',
        ]);

        $response = $this->actingAs($user)
            ->put("/genres/{$genre->id}", [
                'name' => 'ミステリー',
            ]);

        $response->assertSessionHasNoErrors();

        $response->assertRedirect(route('genres.index'));

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => 'ミステリー',
        ]);

        $this->assertDatabaseMissing('genres', [
            'id' => $genre->id,
            'name' => '小説',
        ]);
    }

    public function test_ジャンルを削除できる(): void
    {
        $user = User::factory()->create();

        $genre = Genre::factory()->create([
            'name' => '削除するジャンル',
        ]);

        $response = $this->actingAs($user)
            ->delete("/genres/{$genre->id}");

        $response->assertRedirect(route('genres.index'));

        $this->assertDatabaseMissing('genres', [
            'id' => $genre->id,
            'name' => '削除するジャンル',
        ]);
    }
}