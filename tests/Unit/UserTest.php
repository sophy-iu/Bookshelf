<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\User;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_ユーザーは複数の書籍を持つ(): void
    {
        $user = User::factory()->create();

        Book::factory()->count(2)->create([
            'user_id' => $user->id,
        ]);

        $this->assertCount(2, $user->books);
    }

    public function test_ユーザーは複数のレビューを持つ(): void
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

        $this->assertCount(2, $user->reviews);
    }

    public function test_ユーザーは複数の書籍をお気に入りにできる(): void
    {
        $user = User::factory()->create();

        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();

        $user->favoritebooks()->attach([
            $book1->id,
            $book2->id,
        ]);

        $this->assertCount(2, $user->favoritebooks);

        $this->assertTrue(
            $user->favoritebooks->contains($book1)
        );

        $this->assertTrue(
            $user->favoritebooks->contains($book2)
        );
    }

    public function test_ユーザーは複数のレビューにいいねできる(): void
    {
        $user = User::factory()->create();
        $reviewUser = User::factory()->create();
        $book = Book::factory()->create();

        $review1 = Review::create([
            'user_id' => $reviewUser->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => 'レビュー1',
        ]);

        $review2 = Review::create([
            'user_id' => $reviewUser->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => 'レビュー2',
        ]);

        $user->likedReviews()->attach([
            $review1->id,
            $review2->id,
        ]);

        $this->assertCount(2, $user->likedReviews);

        $this->assertTrue(
            $user->likedReviews->contains($review1)
        );

        $this->assertTrue(
            $user->likedReviews->contains($review2)
        );
    }
}