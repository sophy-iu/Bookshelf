<?php

namespace Tests\Feature;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\Genre;
use App\Models\ReadingPlan;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_ログインユーザーは読書レポート画面を表示できる(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/reports');

        $response->assertStatus(200);
    }

    public function test_レビュー総数と平均評価が正しく集計される(): void
    {
        $user = User::factory()->create();

        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book1->id,
            'rating' => 5,
            'comment' => 'とても良かったです。',
        ]);

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book2->id,
            'rating' => 3,
            'comment' => '普通でした。',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/reports');

        $response->assertStatus(200);

        $response->assertViewHas('stats', function ($stats) {
            return $stats['summary']['total_reviews'] === 2
                && $stats['summary']['average_rating'] == 4;
        });
    }

    public function test_読了済みの読書計画数が正しく集計される(): void
    {
        $user = User::factory()->create();

        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();
        $book3 = Book::factory()->create();

        ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book1->id,
            'target_date' => today()->subDays(10),
            'status' => ReadingPlanStatus::Completed,
            'completed_at' => today()->subDays(5),
        ]);

        ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book2->id,
            'target_date' => today()->subDays(5),
            'status' => ReadingPlanStatus::Completed,
            'completed_at' => today()->subDay(),
        ]);

        ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book3->id,
            'target_date' => today()->addDays(7),
            'status' => ReadingPlanStatus::InProgress,
            'completed_at' => null,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/reports');

        $response->assertStatus(200);

        $response->assertViewHas('stats', function ($stats) {
            return $stats['summary']['books_read'] === 2;
        });
    }

    public function test_評価ごとのレビュー件数が正しく集計される(): void
    {
        $user = User::factory()->create();

        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();
        $book3 = Book::factory()->create();

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book1->id,
            'rating' => 5,
            'comment' => '最高でした。',
        ]);

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book2->id,
            'rating' => 5,
            'comment' => 'とても良かったです。',
        ]);

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book3->id,
            'rating' => 3,
            'comment' => '普通でした。',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/reports');

        $response->assertStatus(200);

        $response->assertViewHas('stats', function ($stats) {
            return $stats['rating_distribution']->all() === [
                0,
                0,
                1,
                0,
                2,
            ];
        });
    }

    public function test_高評価の書籍だけがtop_rated_booksに含まれる(): void
    {
        $user = User::factory()->create();

        $book1 = Book::factory()->create([
            'title' => '評価5の本',
        ]);

        $book2 = Book::factory()->create([
            'title' => '評価4の本',
        ]);

        $book3 = Book::factory()->create([
            'title' => '評価3の本',
        ]);

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book1->id,
            'rating' => 5,
            'comment' => '最高でした。',
        ]);

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book2->id,
            'rating' => 4,
            'comment' => '良かったです。',
        ]);

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book3->id,
            'rating' => 3,
            'comment' => '普通でした。',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/reports');

        $response->assertStatus(200);

        $response->assertViewHas('stats', function ($stats) {
            $topRatedBooks = $stats['top_rated_books'];

            return $topRatedBooks->count() === 2
                && $topRatedBooks->pluck('title')->contains('評価5の本')
                && $topRatedBooks->pluck('title')->contains('評価4の本')
                && ! $topRatedBooks->pluck('title')->contains('評価3の本');
        });
    }

    public function test_ジャンルごとの平均評価が正しく集計される(): void
    {
        $user = User::factory()->create();

        $genre = Genre::create([
            'name' => '技術書',
        ]);

        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();

        $book1->genres()->attach($genre->id);
        $book2->genres()->attach($genre->id);

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book1->id,
            'rating' => 5,
            'comment' => '最高でした。',
        ]);

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book2->id,
            'rating' => 3,
            'comment' => '普通でした。',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/reports');

        $response->assertStatus(200);

        $response->assertViewHas('stats', function ($stats) {
            $genreRating = $stats['genre_ratings']->first();

            return $genreRating['name'] === '技術書'
                && $genreRating['count'] === 2
                && $genreRating['average_rating'] == 4;
        });
    }
}
