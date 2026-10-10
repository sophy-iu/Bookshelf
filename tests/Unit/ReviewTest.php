<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_レビューは投稿したユーザーに属する(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $review = Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => 'とても面白い本でした。',
        ]);

        $this->assertTrue(
            $review->user->is($user)
        );
    }

    public function test_レビューは書籍に属する(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'title' => 'テスト書籍',
        ]);

        $review = Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => 'テストレビューです。',
        ]);

        $this->assertTrue(
            $review->book->is($book)
        );
    }

    public function test_レビューには複数のユーザーがいいねできる(): void
    {
        $reviewUser = User::factory()->create();
        $likeUser1 = User::factory()->create();
        $likeUser2 = User::factory()->create();
        $book = Book::factory()->create();

        $review = Review::create([
            'user_id' => $reviewUser->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => 'いいねされるレビューです。',
        ]);

        $review->likedByUsers()->attach([
            $likeUser1->id,
            $likeUser2->id,
        ]);

        $this->assertCount(2, $review->likedByUsers);

        $this->assertTrue(
            $review->likedByUsers->contains($likeUser1)
        );

        $this->assertTrue(
            $review->likedByUsers->contains($likeUser2)
        );
    }
}