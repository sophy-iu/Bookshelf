<?php

namespace Tests\Feature\Api;

use App\Models\Book;
use App\Models\User;
use App\Models\Genre;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookApiTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_書籍一覧を取得できる(): void
    {
        
        Book::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/books');

        $response->assertStatus(200);

        $response->assertJsonCount(3, 'data');
    }

    public function test_書籍詳細を取得できる(): void
    {
        $book = Book::factory()->create();

        $response = $this->getJson("/api/v1/books/{$book->id}");

        $response->assertStatus(200);

        $response->assertJsonPath('data.id', $book->id);
        $response->assertJsonPath('data.title', $book->title);
        $response->assertJsonPath('data.author', $book->author);
    }

    public function test_未認証ユーザーは書籍を登録できない(): void
    {
        $response = $this->postJson('/api/v1/books', []);

        $response->assertStatus(401);
    }

    public function test_認証済みユーザーは書籍を登録できる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/books', [
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'isbn' => '1234567890123',
            'published_date' => '2026-01-01',
            'description' => 'Laravelの入門書です。',
            'image_url' => 'https://example.com/book.jpg',
            'genres' => [$genre->id],
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('books', [
            'title' => 'Laravel入門',
            'user_id' => $user->id,
        ]);
    }

    public function test_未認証ユーザーは書籍を更新できない(): void
    {
        $book = Book::factory()->create();

        $response = $this->putJson("/api/v1/books/{$book->id}", []);

        $response->assertStatus(401);
    }

    public function test_所有者は書籍を更新できる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson("/api/v1/books/{$book->id}", [
            'title' => '更新後のタイトル',
            'author' => '更新後の著者',
            'isbn' => '1234567890123',
            'published_date' => '2026-01-01',
            'description' => '更新後の説明です。',
            'image_url' => 'https://example.com/updated.jpg',
            'genres' => [$genre->id],
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'user_id' => $user->id,
            'title' => '更新後のタイトル',
            'author' => '更新後の著者',
        ]);
    }
    
    public function test_他人の書籍は更新できない(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $genre = Genre::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $owner->id,
        ]);

        Sanctum::actingAs($otherUser);

        $response = $this->putJson("/api/v1/books/{$book->id}", [
            'title' => '更新後のタイトル',
            'author' => '更新後の著者',
            'isbn' => '1234567890123',
            'published_date' => '2026-01-01',
            'description' => '更新後の説明です。',
            'image_url' => 'https://example.com/book.jpg',
            'genres' => [$genre->id],
        ]);

        $response->assertStatus(403);
    }

    public function test_未認証ユーザーは書籍を削除できない(): void
    {
        $book = Book::factory()->create();

        $response = $this->deleteJson("/api/v1/books/{$book->id}");

        $response->assertStatus(401);
    }

    public function test_所有者は書籍を削除できる(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/v1/books/{$book->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);
    }

    public function test_他人の書籍は削除できない(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $owner->id,
        ]);

        Sanctum::actingAs($otherUser);

        $response = $this->deleteJson("/api/v1/books/{$book->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'user_id' => $owner->id,
        ]);
    }
}
