<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    public function test_書籍をお気に入りに追加できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)
            ->post("/favorites/{$book->id}");

        $response->assertRedirect();

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    public function test_書籍のお気に入りを解除できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $user->favoritebooks()->attach($book->id);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $response = $this->actingAs($user)
            ->post("/favorites/{$book->id}");

        $response->assertRedirect();

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    public function test_お気に入り一覧画面を表示できる(): void
    {
        $user = User::factory()->create();

        $favoriteBook = Book::factory()->create([
            'title' => 'お気に入りの本',
        ]);

        $otherBook = Book::factory()->create([
            'title' => 'お気に入りではない本',
        ]);

        $user->favoritebooks()->attach($favoriteBook->id);

        $response = $this->actingAs($user)
            ->get('/favorites');

        $response->assertStatus(200);

        $response->assertSee('お気に入りの本');

        $response->assertDontSee('お気に入りではない本');
    }
}