<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_書籍一覧画面を表示できる(): void
    {
        $book = Book::factory()->create([
            'title' => '吾輩は猫である',
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('吾輩は猫である');
    }

    public function test_書籍詳細画面を表示できる(): void
    {
        $book = Book::factory()->create([
            'title' => '吾輩は猫である',
            'author' => '夏目漱石',
        ]);

        $response = $this->get("/books/{$book->id}");

        $response->assertStatus(200);
        $response->assertSee('吾輩は猫である');
        $response->assertSee('夏目漱石');
    }

    public function test_ログインユーザーは書籍を登録できる(): void
    {
        $user = User::factory()->create();

        $genre = Genre::factory()->create([
            'name' => '小説',
        ]);

        $response = $this->actingAs($user)->post('/books', [
            'title' => '吾輩は猫である',
            'author' => '夏目漱石',
            'isbn' => '9784101010014',
            'published_date' => '1905-01-01',
            'description' => '猫の視点から描かれた小説です',
            'image_url' => null,
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasNoErrors();

        $response->assertRedirect(route('books.index'));

        $this->assertDatabaseHas('books', [
            'user_id' => $user->id,
            'title' => '吾輩は猫である',
            'author' => '夏目漱石',
            'isbn' => '9784101010014',
        ]);
    }

    public function test_必須項目が空の場合は書籍を登録できない(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/books', [
            'title' => '',
            'author' => '',
            'isbn' => '',
            'published_date' => '',
            'description' => null,
            'image_url' => null,
            'genres' => [],
        ]);

        $response->assertSessionHasErrors([
            'title',
            'author',
            'isbn',
            'published_date',
            'genres',
        ]);

        $this->assertDatabaseCount('books', 0);
    }

    public function test_isb_nが13桁でない場合は書籍を登録できない(): void
    {
        $user = User::factory()->create();

        $genre = Genre::factory()->create([
            'name' => '小説',
        ]);

        $response = $this->actingAs($user)->post('/books', [
            'title' => '吾輩は猫である',
            'author' => '夏目漱石',
            'isbn' => '123456789012', // 12桁
            'published_date' => '1905-01-01',
            'description' => '猫の視点から描かれた小説です',
            'image_url' => null,
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors([
            'isbn',
        ]);

        $this->assertDatabaseCount('books', 0);
    }

    public function test_isb_nが重複している場合は書籍を登録できない(): void
    {
        $user = User::factory()->create();

        $genre = Genre::factory()->create([
            'name' => '小説',
        ]);

        Book::factory()->create([
            'isbn' => '9784101010014',
        ]);

        $response = $this->actingAs($user)->post('/books', [
            'title' => '新しい書籍',
            'author' => 'テスト著者',
            'isbn' => '9784101010014',
            'published_date' => '2020-01-01',
            'description' => 'テスト用の説明です',
            'image_url' => null,
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors([
            'isbn',
        ]);

        $this->assertDatabaseCount('books', 1);
    }

    public function test_自分の書籍の編集画面を表示できる(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '吾輩は猫である',
        ]);

        $response = $this->actingAs($user)
            ->get("/books/{$book->id}/edit");

        $response->assertStatus(200);
        $response->assertSee('吾輩は猫である');
    }

    public function test_他人の書籍の編集画面にはアクセスできない(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $owner->id,
            'title' => '吾輩は猫である',
        ]);

        $response = $this->actingAs($otherUser)
            ->get("/books/{$book->id}/edit");

        $response->assertStatus(403);
    }

    public function test_自分の書籍を更新できる(): void
    {

        $user = User::factory()->create();

        $oldGenre = Genre::factory()->create([
            'name' => '小説',
        ]);

        $newGenre = Genre::factory()->create([
            'name' => '文学',
        ]);

        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '変更前タイトル',
            'isbn' => '9784101010014',
        ]);

        $book->genres()->attach($oldGenre->id);

        $response = $this->actingAs($user)
            ->put("/books/{$book->id}/update", [
                'title' => '変更後タイトル',
                'author' => '夏目漱石',
                'isbn' => '9784101010014',
                'published_date' => '1905-01-01',
                'description' => '変更後の説明です',
                'image_url' => null,
                'genres' => [$newGenre->id],
            ]);

        $response->assertSessionHasNoErrors();

        $response->assertRedirect(route('books.show', $book));

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '変更後タイトル',
            'author' => '夏目漱石',
            'isbn' => '9784101010014',
        ]);

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $newGenre->id,
        ]);

        $this->assertDatabaseMissing('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $oldGenre->id,
        ]);
    }

    public function test_他人の書籍を更新できない(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $genre = Genre::factory()->create([
            'name' => '小説',
        ]);

        $book = Book::factory()->create([
            'user_id' => $owner->id,
            'title' => '変更前タイトル',
            'isbn' => '9784101010014',
        ]);

        $book->genres()->attach($genre->id);

        $response = $this->actingAs($otherUser)
            ->put("/books/{$book->id}/update", [
                'title' => '勝手に変更したタイトル',
                'author' => '夏目漱石',
                'isbn' => '9784101010014',
                'published_date' => '1905-01-01',
                'description' => '変更しようとしています',
                'image_url' => null,
                'genres' => [$genre->id],
            ]);

        $response->assertStatus(403);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '変更前タイトル',
        ]);

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
            'title' => '勝手に変更したタイトル',
        ]);
    }

    public function test_自分の書籍を削除できる(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '削除する書籍',
        ]);

        $response = $this->actingAs($user)
            ->delete("/books/{$book->id}");

        $response->assertRedirect(route('books.index'));

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);
    }

    public function test_他人の書籍を削除できない(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $owner->id,
            'title' => '削除してはいけない書籍',
        ]);

        $response = $this->actingAs($otherUser)
            ->delete("/books/{$book->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '削除してはいけない書籍',
        ]);
    }

    public function test_キーワードで書籍を検索できる(): void
    {
        Book::factory()->create([
            'title' => '吾輩は猫である',
            'author' => '夏目漱石',
        ]);

        Book::factory()->create([
            'title' => '走れメロス',
            'author' => '太宰治',
        ]);

        $response = $this->get('/?keyword=猫');

        $response->assertStatus(200);

        $response->assertSee('吾輩は猫である');
        $response->assertDontSee('走れメロス');
    }

    public function test_著者名で書籍を検索できる(): void
    {
        Book::factory()->create([
            'title' => '吾輩は猫である',
            'author' => '夏目漱石',
        ]);

        Book::factory()->create([
            'title' => '走れメロス',
            'author' => '太宰治',
        ]);

        $response = $this->get('/?keyword=夏目');

        $response->assertStatus(200);

        $response->assertSee('吾輩は猫である');
        $response->assertDontSee('走れメロス');
    }

    public function test_ジャンルで書籍を絞り込める(): void
    {
        $novel = Genre::factory()->create([
            'name' => '小説',
        ]);

        $technical = Genre::factory()->create([
            'name' => '技術書',
        ]);

        $novelBook = Book::factory()->create([
            'title' => '吾輩は猫である',
        ]);

        $technicalBook = Book::factory()->create([
            'title' => 'Laravel入門',
        ]);

        $novelBook->genres()->attach($novel->id);
        $technicalBook->genres()->attach($technical->id);

        $response = $this->get("/?genre={$novel->id}");

        $response->assertStatus(200);

        $response->assertSee('吾輩は猫である');
        $response->assertDontSee('Laravel入門');
    }

    public function test_キーワードとジャンルを組み合わせて書籍を絞り込める(): void
    {
        $novel = Genre::factory()->create([
            'name' => '小説',
        ]);

        $technical = Genre::factory()->create([
            'name' => '技術書',
        ]);

        $book1 = Book::factory()->create([
            'title' => '猫の物語',
            'author' => '著者A',
        ]);

        $book2 = Book::factory()->create([
            'title' => '犬の物語',
            'author' => '著者B',
        ]);

        $book3 = Book::factory()->create([
            'title' => '猫でもわかるLaravel',
            'author' => '著者C',
        ]);

        $book1->genres()->attach($novel->id);
        $book2->genres()->attach($novel->id);
        $book3->genres()->attach($technical->id);

        $response = $this->get("/?keyword=猫&genre={$novel->id}");

        $response->assertStatus(200);

        $response->assertSee('猫の物語');
        $response->assertDontSee('犬の物語');
        $response->assertDontSee('猫でもわかるLaravel');
    }

    public function test_古い書籍順に並び替えできる(): void
    {
        Book::factory()->create([
            'title' => '新しい本',
            'created_at' => now(),
        ]);

        Book::factory()->create([
            'title' => '古い本',
            'created_at' => now()->subDays(10),
        ]);

        $response = $this->get('/?sort=oldest');

        $response->assertStatus(200);

        $response->assertSeeInOrder([
            '古い本',
            '新しい本',
        ]);
    }

    public function test_評価が高い書籍順に並び替えできる(): void
    {
        $user = User::factory()->create();

        $highRatedBook = Book::factory()->create([
            'title' => '高評価の本',
        ]);

        $lowRatedBook = Book::factory()->create([
            'title' => '低評価の本',
        ]);

        Review::create([
            'user_id' => $user->id,
            'book_id' => $highRatedBook->id,
            'rating' => 5,
            'comment' => 'とても良かったです。',
        ]);

        Review::create([
            'user_id' => $user->id,
            'book_id' => $lowRatedBook->id,
            'rating' => 2,
            'comment' => 'いまひとつでした。',
        ]);

        $response = $this->get('/?sort=rating');

        $response->assertStatus(200);

        $response->assertSeeInOrder([
            '高評価の本',
            '低評価の本',
        ]);
    }

    public function test_タイトル順に並び替えできる(): void
    {
        Book::factory()->create([
            'title' => 'Cの本',
        ]);

        Book::factory()->create([
            'title' => 'Aの本',
        ]);

        Book::factory()->create([
            'title' => 'Bの本',
        ]);

        $response = $this->get('/?sort=title');

        $response->assertStatus(200);

        $response->assertSeeInOrder([
            'Aの本',
            'Bの本',
            'Cの本',
        ]);
    }

    public function test_ページネーションで検索条件が維持される(): void
    {
        for ($i = 1; $i <= 11; $i++) {
            Book::factory()->create([
                'title' => "Laravel入門{$i}",
                'author' => 'テスト著者',
            ]);
        }

        $response = $this->get('/?keyword=Laravel&sort=title');

        $response->assertStatus(200);

        $response->assertSee(
            'keyword=Laravel&amp;sort=title&amp;page=2',
            false
        );
    }

    public function test_isb_nから_google_books_ap_iの書籍情報を取得できる(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([
                'items' => [
                    [
                        'volumeInfo' => [
                            'title' => '吾輩は猫である',
                            'authors' => ['夏目漱石'],
                            'publishedDate' => '1905-01-01',
                            'description' => '猫の視点から描かれた小説です。',
                            'imageLinks' => [
                                'thumbnail' => 'https://example.com/cat.jpg',
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this
            ->actingAs($user)
            ->getJson('/books/isbn/9784101010014');

        $response->assertStatus(200);

        $response->assertJson([
            'title' => '吾輩は猫である',
            'author' => '夏目漱石',
            'published_date' => '1905-01-01',
            'description' => '猫の視点から描かれた小説です。',
            'image_url' => 'https://example.com/cat.jpg',
        ]);
    }

    public function test_google_books_ap_iでエラーが発生した場合は500を返す(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response(
                [],
                500
            ),
        ]);

        $response = $this
            ->actingAs($user)
            ->getJson('/books/isbn/9784101010014');

        $response->assertStatus(500);

        $response->assertJson([
            'message' => 'Google Books APIの取得に失敗しました。',
        ]);
    }

    public function test_isb_nに該当する書籍が見つからない場合は404を返す(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([
                'totalItems' => 0,
                'items' => [],
            ], 200),
        ]);

        $response = $this
            ->actingAs($user)
            ->getJson('/books/isbn/9784101010014');

        $response->assertStatus(404);

        $response->assertJson([
            'message' => '書籍が見つかりませんでした。',
        ]);
    }

    public function test_google_books_ap_iに_isb_nと_ap_iキーを送信している(): void
    {
        $user = User::factory()->create();

        config([
            'services.google_books.api_key' => 'test-api-key',
        ]);

        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([
                'items' => [
                    [
                        'volumeInfo' => [
                            'title' => '吾輩は猫である',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $this
            ->actingAs($user)
            ->getJson('/books/isbn/9784101010014')
            ->assertStatus(200);

        Http::assertSent(function ($request) {
            return str_starts_with(
                $request->url(),
                'https://www.googleapis.com/books/v1/volumes?'
            )
                && $request['q'] === 'isbn:9784101010014'
                && $request['key'] === 'test-api-key';
        });
    }

    public function test_google_books_ap_iの書籍情報に一部項目がなくても取得できる(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([
                'items' => [
                    [
                        'volumeInfo' => [
                            'title' => '吾輩は猫である',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this
            ->actingAs($user)
            ->getJson('/books/isbn/9784101010014');

        $response->assertStatus(200);

        $response->assertJson([
            'title' => '吾輩は猫である',
            'author' => null,
            'published_date' => null,
            'description' => null,
            'image_url' => null,
        ]);
    }

    public function test_レビューがある書籍だけランキングに表示される(): void
    {
        $user = User::factory()->create();

        $reviewedBook = Book::factory()->create([
            'title' => 'レビューありの本',
        ]);

        Book::factory()->create([
            'title' => 'レビューなしの本',
        ]);

        Review::create([
            'user_id' => $user->id,
            'book_id' => $reviewedBook->id,
            'rating' => 5,
            'comment' => 'とても良かったです。',
        ]);

        $response = $this->get('/ranking');

        $response->assertStatus(200);

        $response->assertSee('レビューありの本');
        $response->assertDontSee('レビューなしの本');
    }

    public function test_ランキングは平均評価が高い書籍順に表示される(): void
    {
        $user = User::factory()->create();

        $highRatedBook = Book::factory()->create([
            'title' => '高評価の本',
        ]);

        $lowRatedBook = Book::factory()->create([
            'title' => '低評価の本',
        ]);

        Review::create([
            'user_id' => $user->id,
            'book_id' => $highRatedBook->id,
            'rating' => 5,
            'comment' => '最高でした。',
        ]);

        Review::create([
            'user_id' => $user->id,
            'book_id' => $lowRatedBook->id,
            'rating' => 2,
            'comment' => 'いまひとつでした。',
        ]);

        $response = $this->get('/ranking');

        $response->assertStatus(200);

        $response->assertSeeInOrder([
            '高評価の本',
            '低評価の本',
        ]);
    }

    public function test_ランキングには上位10冊まで表示される(): void
    {
        $user = User::factory()->create();

        for ($i = 1; $i <= 10; $i++) {
            $book = Book::factory()->create([
                'title' => "ランキング対象{$i}",
            ]);

            Review::create([
                'user_id' => $user->id,
                'book_id' => $book->id,
                'rating' => 5,
                'comment' => '高評価です。',
            ]);
        }

        $eleventhBook = Book::factory()->create([
            'title' => 'ランキング11位の本',
        ]);

        Review::create([
            'user_id' => $user->id,
            'book_id' => $eleventhBook->id,
            'rating' => 1,
            'comment' => '低評価です。',
        ]);

        $response = $this->get('/ranking');

        $response->assertStatus(200);

        $response->assertSee('ランキング対象1');
        $response->assertSee('ランキング対象10');

        $response->assertDontSee('ランキング11位の本');
    }

    public function test_ログインユーザーは書籍登録画面を表示できる(): void
    {
        $user = User::factory()->create();

        Genre::factory()->create([
            'name' => '小説',
        ]);

        Genre::factory()->create([
            'name' => '技術書',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/books/create');

        $response->assertStatus(200);

        $response->assertViewIs('books.create');

        $response->assertViewHas('genres');

        $response->assertSee('小説');
        $response->assertSee('技術書');
    }
}
