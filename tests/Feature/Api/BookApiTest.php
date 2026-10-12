<?php

namespace Tests\Feature\Api;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
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

    public function test_書籍登録時に必須項目がない場合は422になる(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/books', []);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'title',
            'author',
            'genres',
        ]);
    }

    public function test_isb_nが13桁でない場合は422になる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/books', [
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'isbn' => '123456789012',
            'published_date' => '2026-01-01',
            'description' => 'Laravelの入門書です。',
            'image_url' => 'https://example.com/book.jpg',
            'genres' => [$genre->id],
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'isbn',
        ]);
    }

    public function test_同じ_isb_nの書籍は登録できない(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        Book::factory()->create([
            'isbn' => '1234567890123',
        ]);

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

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'isbn',
        ]);

        $response->assertJsonPath(
            'errors.isbn.0',
            'このISBNはすでに登録されています。'
        );
    }

    public function test_書籍更新時は現在の_isb_nをそのまま使用できる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
            'isbn' => '1234567890123',
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

        $response->assertJsonMissingValidationErrors([
            'isbn',
        ]);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'isbn' => '1234567890123',
            'title' => '更新後のタイトル',
        ]);
    }

    public function test_書籍更新時に他の書籍と同じ_isb_nは使用できない(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        // 更新対象の書籍
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'isbn' => '1111111111111',
        ]);

        // 別の書籍
        Book::factory()->create([
            'isbn' => '2222222222222',
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson("/api/v1/books/{$book->id}", [
            'title' => '更新後のタイトル',
            'author' => '更新後の著者',

            // 別の書籍が使用しているISBN
            'isbn' => '2222222222222',

            'published_date' => '2026-01-01',
            'description' => '更新後の説明です。',
            'image_url' => 'https://example.com/updated.jpg',
            'genres' => [$genre->id],
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'isbn',
        ]);

        $response->assertJsonPath(
            'errors.isbn.0',
            'このISBNはすでに登録されています。'
        );
    }

    public function test_存在しないジャンルを指定した場合は422になる(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/books', [
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'isbn' => '1234567890123',
            'published_date' => '2026-01-01',
            'description' => 'Laravelの入門書です。',
            'image_url' => 'https://example.com/book.jpg',

            // 存在しないジャンルID
            'genres' => [99999],
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'genres.0',
        ]);

        $this->assertSame(
            '指定されたジャンルが存在しません。',
            $response->json('errors')['genres.0'][0]
        );
    }

    public function test_書籍登録時にジャンルも紐付けられる(): void
    {
        $user = User::factory()->create();

        $genre1 = Genre::factory()->create();
        $genre2 = Genre::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/books', [
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'isbn' => '1234567890123',
            'published_date' => '2026-01-01',
            'description' => 'Laravelの入門書です。',
            'image_url' => 'https://example.com/book.jpg',
            'genres' => [
                $genre1->id,
                $genre2->id,
            ],
        ]);

        $response->assertStatus(201);

        $bookId = $response->json('data.id');

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $bookId,
            'genre_id' => $genre1->id,
        ]);

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $bookId,
            'genre_id' => $genre2->id,
        ]);
    }

    public function test_書籍更新時にジャンルを変更できる(): void
    {
        $user = User::factory()->create();

        $oldGenre = Genre::factory()->create();
        $newGenre1 = Genre::factory()->create();
        $newGenre2 = Genre::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
            'isbn' => '1234567890123',
        ]);

        // 更新前のジャンルを紐付ける
        $book->genres()->attach($oldGenre->id);

        Sanctum::actingAs($user);

        $response = $this->putJson("/api/v1/books/{$book->id}", [
            'title' => '更新後のタイトル',
            'author' => '更新後の著者',
            'isbn' => '1234567890123',
            'published_date' => '2026-01-01',
            'description' => '更新後の説明です。',
            'image_url' => 'https://example.com/updated.jpg',
            'genres' => [
                $newGenre1->id,
                $newGenre2->id,
            ],
        ]);

        $response->assertStatus(200);

        // 古いジャンルが外れている
        $this->assertDatabaseMissing('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $oldGenre->id,
        ]);

        // 新しいジャンル1が登録されている
        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $newGenre1->id,
        ]);

        // 新しいジャンル2が登録されている
        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $newGenre2->id,
        ]);
    }

    public function test_キーワードで書籍を検索できる(): void
    {
        Book::factory()->create([
            'title' => 'Laravel入門',
            'author' => '山田太郎',
        ]);

        Book::factory()->create([
            'title' => 'PHP基礎',
            'author' => '鈴木花子',
        ]);

        $response = $this->getJson(
            '/api/v1/books?keyword=Laravel'
        );

        $response->assertStatus(200);

        $response->assertJsonCount(1, 'data');

        $response->assertJsonPath(
            'data.0.title',
            'Laravel入門'
        );
    }

    public function test_著者名で書籍を検索できる(): void
    {
        Book::factory()->create([
            'title' => 'Laravel入門',
            'author' => '山田太郎',
        ]);

        Book::factory()->create([
            'title' => 'PHP基礎',
            'author' => '鈴木花子',
        ]);

        $response = $this->getJson(
            '/api/v1/books?keyword=鈴木'
        );

        $response->assertStatus(200);

        $response->assertJsonCount(1, 'data');

        $response->assertJsonPath(
            'data.0.title',
            'PHP基礎'
        );

        $response->assertJsonPath(
            'data.0.author',
            '鈴木花子'
        );
    }

    public function test_ジャンルで書籍を絞り込める(): void
    {
        $genre1 = Genre::factory()->create();
        $genre2 = Genre::factory()->create();

        $book1 = Book::factory()->create([
            'title' => 'Laravel入門',
        ]);

        $book2 = Book::factory()->create([
            'title' => '小説入門',
        ]);

        $book1->genres()->attach($genre1->id);
        $book2->genres()->attach($genre2->id);

        $response = $this->getJson(
            "/api/v1/books?genre_id={$genre1->id}"
        );

        $response->assertStatus(200);

        $response->assertJsonCount(1, 'data');

        $response->assertJsonPath(
            'data.0.id',
            $book1->id
        );

        $response->assertJsonPath(
            'data.0.title',
            'Laravel入門'
        );
    }

    public function test_存在しないジャンル_i_dで絞り込むと422になる(): void
    {
        $response = $this->getJson(
            '/api/v1/books?genre_id=99999'
        );

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'genre_id',
        ]);
    }

    public function test_1ページあたりの書籍件数を指定できる(): void
    {
        Book::factory()->count(5)->create();

        $response = $this->getJson(
            '/api/v1/books?per_page=2'
        );

        $response->assertStatus(200);

        $response->assertJsonCount(2, 'data');

        $response->assertJsonPath(
            'meta.per_page',
            2
        );

        $response->assertJsonPath(
            'meta.total',
            5
        );
    }

    public function test_書籍一覧の2ページ目を取得できる(): void
    {
        Book::factory()->count(5)->create();

        $response = $this->getJson(
            '/api/v1/books?per_page=2&page=2'
        );

        $response->assertStatus(200);

        $response->assertJsonCount(2, 'data');

        $response->assertJsonPath(
            'meta.current_page',
            2
        );

        $response->assertJsonPath(
            'meta.per_page',
            2
        );

        $response->assertJsonPath(
            'meta.total',
            5
        );
    }

    public function test_per_pageが100を超える場合は422になる(): void
    {
        $response = $this->getJson(
            '/api/v1/books?per_page=101'
        );

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'per_page',
        ]);
    }

    public function test_pageが0の場合は422になる(): void
    {
        $response = $this->getJson(
            '/api/v1/books?page=0'
        );

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'page',
        ]);
    }

    public function test_存在しない書籍の詳細を取得すると404になる(): void
    {
        $response = $this->getJson(
            '/api/v1/books/99999'
        );

        $response->assertStatus(404);
    }

    public function test_書籍一覧で平均評価とレビュー件数を取得できる(): void
    {
        $book = Book::factory()->create();

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Review::create([
            'user_id' => $user1->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => 'とても良かったです。',
        ]);

        Review::create([
            'user_id' => $user2->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => '普通でした。',
        ]);

        $response = $this->getJson('/api/v1/books');

        $response->assertStatus(200);

        $response->assertJsonPath(
            'data.0.reviews_count',
            2
        );

        $response->assertJsonPath(
            'data.0.average_rating',
            4
        );
    }

    public function test_書籍詳細でレビュー情報を取得できる(): void
    {
        $book = Book::factory()->create();

        $user = User::factory()->create([
            'name' => '山田太郎',
        ]);

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => 'とても分かりやすい本でした。',
        ]);

        $response = $this->getJson(
            "/api/v1/books/{$book->id}"
        );

        $response->assertStatus(200);

        $response->assertJsonPath(
            'data.reviews.0.user_name',
            '山田太郎'
        );

        $response->assertJsonPath(
            'data.reviews.0.rating',
            5
        );

        $response->assertJsonPath(
            'data.reviews.0.comment',
            'とても分かりやすい本でした。'
        );
    }

    public function test_書籍詳細のレビューに投稿日が含まれる(): void
    {
        $book = Book::factory()->create();
        $user = User::factory()->create();

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => '参考になりました。',
        ]);

        $response = $this->getJson(
            "/api/v1/books/{$book->id}"
        );

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                'reviews' => [
                    '*' => [
                        'user_name',
                        'rating',
                        'comment',
                        'created_at',
                    ],
                ],
            ],
        ]);
    }

    public function test_書籍詳細でジャンル情報を取得できる(): void
    {
        $book = Book::factory()->create();

        $genre1 = Genre::factory()->create([
            'name' => '技術書',
        ]);

        $genre2 = Genre::factory()->create([
            'name' => 'ビジネス',
        ]);

        $book->genres()->attach([
            $genre1->id,
            $genre2->id,
        ]);

        $response = $this->getJson(
            "/api/v1/books/{$book->id}"
        );

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'id' => $genre1->id,
            'name' => '技術書',
        ]);

        $response->assertJsonFragment([
            'id' => $genre2->id,
            'name' => 'ビジネス',
        ]);
    }

    public function test_レビューがない書籍は平均評価がnullでレビュー件数が0になる(): void
    {
        Book::factory()->create();

        $response = $this->getJson('/api/v1/books');

        $response->assertStatus(200);

        $response->assertJsonPath(
            'data.0.average_rating',
            null
        );

        $response->assertJsonPath(
            'data.0.reviews_count',
            0
        );
    }
}
