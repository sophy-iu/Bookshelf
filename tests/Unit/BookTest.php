<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    public function test_書籍は登録ユーザーに属する(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertTrue($book->user->is($user));
    }

    public function test_書籍は複数のジャンルを持つ(): void
    {
        $book = Book::factory()->create();

        $genre1 = Genre::factory()->create([
            'name' => '小説',
        ]);

        $genre2 = Genre::factory()->create([
            'name' => 'ミステリー',
        ]);

        $book->genres()->attach([
            $genre1->id,
            $genre2->id,
        ]);

        $this->assertCount(2, $book->genres);

        $this->assertTrue(
            $book->genres->contains($genre1)
        );

        $this->assertTrue(
            $book->genres->contains($genre2)
        );
    }

    public function test_書籍は複数のレビューを持つ(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => 'レビュー1',
        ]);

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => 'レビュー2',
        ]);

        $this->assertCount(2, $book->reviews);
    }
}