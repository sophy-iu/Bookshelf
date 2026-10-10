<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_ログインユーザーはレビューを投稿できる(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create();

        $response = $this->actingAs($user)
            ->post("/books/{$book->id}/reviews", [
                'rating' => 5,
                'comment' => 'とても面白い本でした。',
            ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => 'とても面白い本でした。',
        ]);
    }

    public function test_評価が1から5以外の場合はレビューを投稿できない(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)
            ->post("/books/{$book->id}/reviews", [
                'rating' => 6,
                'comment' => 'テストレビューです。',
            ]);

        $response->assertSessionHasErrors([
            'rating',
        ]);

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_自分のレビューの編集画面を表示できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $review = Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => '編集前のレビューです。',
        ]);

        $response = $this->actingAs($user)
            ->get("/reviews/{$review->id}/edit");

        $response->assertStatus(200);
        $response->assertSee('編集前のレビューです。');
    }

    public function test_他人のレビューの編集画面にはアクセスできない(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create();

        $review = Review::create([
            'user_id' => $otherUser->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => '他人のレビューです。',
        ]);

        $response = $this->actingAs($user)
            ->get("/reviews/{$review->id}/edit");

        $response->assertStatus(403);
    }

    public function test_自分のレビューを更新できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $review = Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => '編集前のレビューです。',
        ]);

        $response = $this->actingAs($user)
            ->put("/reviews/{$review->id}", [
                'rating' => 5,
                'comment' => '編集後のレビューです。',
            ]);

        $response->assertSessionHasNoErrors();

        $response->assertRedirect(
            route('books.show', $book->id)
        );

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 5,
            'comment' => '編集後のレビューです。',
        ]);

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
            'rating' => 3,
            'comment' => '編集前のレビューです。',
        ]);
    }

    public function test_他人のレビューを更新できない(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create();

        $review = Review::create([
            'user_id' => $otherUser->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => '他人のレビューです。',
        ]);

        $response = $this->actingAs($user)
            ->put("/reviews/{$review->id}", [
                'rating' => 5,
                'comment' => '勝手に変更しました。',
            ]);

        $response->assertStatus(403);

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'user_id' => $otherUser->id,
            'rating' => 3,
            'comment' => '他人のレビューです。',
        ]);
    }

    public function test_自分のレビューを削除できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $review = Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => '削除するレビューです。',
        ]);

        $response = $this->actingAs($user)
            ->delete("/reviews/{$review->id}");

        $response->assertRedirect(
            route('books.show', $book->id)
        );

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);
    }

    public function test_他人のレビューを削除できない(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create();

        $review = Review::create([
            'user_id' => $otherUser->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => '他人のレビューです。',
        ]);

        $response = $this->actingAs($user)
            ->delete("/reviews/{$review->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'user_id' => $otherUser->id,
            'rating' => 5,
            'comment' => '他人のレビューです。',
        ]);
    }

    public function test_レビューにいいねできる(): void
    {
        $user = User::factory()->create();
        $reviewUser = User::factory()->create();
        $book = Book::factory()->create();

        $review = Review::create([
            'user_id' => $reviewUser->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => 'いいね対象のレビューです。',
        ]);

        $response = $this->actingAs($user)
            ->post("/reviews/{$review->id}/like");

        $response->assertRedirect();

        $this->assertDatabaseHas('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }

    public function test_レビューのいいねを解除できる(): void
    {
        $user = User::factory()->create();
        $reviewUser = User::factory()->create();
        $book = Book::factory()->create();

        $review = Review::create([
            'user_id' => $reviewUser->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => 'いいね解除対象のレビューです。',
        ]);

        $user->likedReviews()->attach($review->id);

        $this->assertDatabaseHas('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);

        $response = $this->actingAs($user)
            ->post("/reviews/{$review->id}/like");

        $response->assertRedirect();

        $this->assertDatabaseMissing('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }
}