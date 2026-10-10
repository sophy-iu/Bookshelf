<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_ISBNが13桁でない場合は書籍を登録できない(): void
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

    public function test_ISBNが重複している場合は書籍を登録できない(): void
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
}
