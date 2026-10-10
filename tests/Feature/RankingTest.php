<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Book;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingTest extends TestCase
{
    use RefreshDatabase;

    public function test_ランキング画面を表示できる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/ranking');

        $response->assertStatus(200);
    }

    public function test_平均評価が高い本から順番に表示される(): void
    {
        $user = User::factory()->create();

        $highBook = Book::factory()->create([
            'title' => '高評価の本',
        ]);

        $middleBook = Book::factory()->create([
            'title' => '中評価の本',
        ]);

        $lowBook = Book::factory()->create([
            'title' => '低評価の本',
        ]);

        Review::create([
            'user_id' => $user->id,
            'book_id' => $highBook->id,
            'rating' => 5,
            'comment' => '高評価レビュー',
        ]);

        Review::create([
            'user_id' => $user->id,
            'book_id' => $middleBook->id,
            'rating' => 4,
            'comment' => '中評価レビュー',
        ]);

        Review::create([
            'user_id' => $user->id,
            'book_id' => $lowBook->id,
            'rating' => 3,
            'comment' => '低評価レビュー',
        ]);

        $response = $this->actingAs($user)
            ->get('/ranking');

        $response->assertStatus(200);

        $response->assertSeeInOrder([
            '高評価の本',
            '中評価の本',
            '低評価の本',
        ]);
    }

    public function test_レビューがない本はランキングに表示されない(): void
    {
        $user = User::factory()->create();

        $reviewedBook = Book::factory()->create([
            'title' => 'レビューありの本',
        ]);

        $noReviewBook = Book::factory()->create([
            'title' => 'レビューなしの本',
        ]);

        Review::create([
            'user_id' => $user->id,
            'book_id' => $reviewedBook->id,
            'rating' => 5,
            'comment' => 'レビューがあります。',
        ]);

        $response = $this->actingAs($user)
            ->get('/ranking');

        $response->assertStatus(200);

        $response->assertSee('レビューありの本');

        $response->assertDontSee('レビューなしの本');
    }

    public function test_ランキングは上位10冊まで表示される(): void
    {
        $user = User::factory()->create();

        for ($i = 1; $i <= 11; $i++) {
            $book = Book::factory()->create([
                'title' => "ランキング本{$i}",
            ]);

            Review::create([
                'user_id' => $user->id,
                'book_id' => $book->id,
                'rating' => 5,
                'comment' => "レビュー{$i}",
            ]);
        }

        $response = $this->actingAs($user)
            ->get('/ranking');

        $response->assertStatus(200);

        $response->assertViewHas('rankedBooks', function ($rankedBooks) {
            return $rankedBooks->count() === 10;
        });
    }
}