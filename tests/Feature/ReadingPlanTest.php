<?php

namespace Tests\Feature;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_ログインユーザーは読書計画一覧を表示できる(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/reading-plans');

        $response->assertStatus(200);
    }

    public function test_自分の読書計画だけが一覧に表示される(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $myBook = Book::factory()->create([
            'title' => '自分の読書計画の本',
        ]);

        $otherBook = Book::factory()->create([
            'title' => '他人の読書計画の本',
        ]);

        ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $myBook->id,
            'target_date' => today()->addDays(7),
            'status' => ReadingPlanStatus::InProgress,
        ]);

        ReadingPlan::create([
            'user_id' => $otherUser->id,
            'book_id' => $otherBook->id,
            'target_date' => today()->addDays(7),
            'status' => ReadingPlanStatus::InProgress,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/reading-plans');

        $response->assertStatus(200);

        $response->assertSee('自分の読書計画の本');

        $response->assertDontSee('他人の読書計画の本');
    }

    public function test_ステータスで読書計画を絞り込める(): void
    {
        $user = User::factory()->create();

        $inProgressBook = Book::factory()->create([
            'title' => '進行中の本',
        ]);

        $completedBook = Book::factory()->create([
            'title' => '読了済みの本',
        ]);

        ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $inProgressBook->id,
            'target_date' => today()->addDays(7),
            'status' => ReadingPlanStatus::InProgress,
        ]);

        ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $completedBook->id,
            'target_date' => today(),
            'status' => ReadingPlanStatus::Completed,
            'completed_at' => now(),
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/reading-plans?status=in_progress');

        $response->assertStatus(200);

        $response->assertSee('進行中の本');

        $response->assertDontSee('読了済みの本');
    }

    public function test_読書計画を新規登録できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post('/reading-plans', [
                'book_id' => $book->id,
                'target_date' => today()->addDays(7)->format('Y-m-d'),
            ]);

        $response->assertRedirect(
            route('reading-plans.index')
        );

        $this->assertDatabaseHas('reading_plans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => today()->addDays(7)->format('Y-m-d'),
            'status' => ReadingPlanStatus::InProgress->value,
        ]);
    }

    public function test_過去の日付では読書計画を登録できない(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post('/reading-plans', [
                'book_id' => $book->id,
                'target_date' => today()->subDay()->format('Y-m-d'),
            ]);

        $response->assertSessionHasErrors([
            'target_date',
        ]);

        $this->assertDatabaseMissing('reading_plans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    public function test_存在しない書籍では読書計画を登録できない(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post('/reading-plans', [
                'book_id' => 99999,
                'target_date' => today()->addDays(7)->format('Y-m-d'),
            ]);

        $response->assertSessionHasErrors([
            'book_id',
        ]);

        $this->assertDatabaseMissing('reading_plans', [
            'user_id' => $user->id,
            'book_id' => 99999,
        ]);
    }

    public function test_自分の読書計画を更新できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => today()->addDays(7),
            'status' => ReadingPlanStatus::InProgress,
        ]);

        $newTargetDate = today()->addDays(14)->format('Y-m-d');

        $response = $this
            ->actingAs($user)
            ->put("/reading-plans/{$readingPlan->id}", [
                'target_date' => $newTargetDate,
            ]);

        $response->assertRedirect(
            route('reading-plans.index')
        );

        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'user_id' => $user->id,
            'target_date' => $newTargetDate,
        ]);
    }

    public function test_他人の読書計画は更新できない(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create();

        $readingPlan = ReadingPlan::create([
            'user_id' => $owner->id,
            'book_id' => $book->id,
            'target_date' => today()->addDays(7),
            'status' => ReadingPlanStatus::InProgress,
        ]);

        $newTargetDate = today()->addDays(14)->format('Y-m-d');

        $response = $this
            ->actingAs($otherUser)
            ->put("/reading-plans/{$readingPlan->id}", [
                'target_date' => $newTargetDate,
            ]);

        $response->assertStatus(403);

        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'user_id' => $owner->id,
            'target_date' => today()->addDays(7)->format('Y-m-d'),
        ]);
    }

    public function test_自分の読書計画を削除できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => today()->addDays(7),
            'status' => ReadingPlanStatus::InProgress,
        ]);

        $response = $this
            ->actingAs($user)
            ->delete("/reading-plans/{$readingPlan->id}");

        $response->assertRedirect(
            route('reading-plans.index')
        );

        $this->assertDatabaseMissing('reading_plans', [
            'id' => $readingPlan->id,
        ]);
    }

    public function test_他人の読書計画は削除できない(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create();

        $readingPlan = ReadingPlan::create([
            'user_id' => $owner->id,
            'book_id' => $book->id,
            'target_date' => today()->addDays(7),
            'status' => ReadingPlanStatus::InProgress,
        ]);

        $response = $this
            ->actingAs($otherUser)
            ->delete("/reading-plans/{$readingPlan->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'user_id' => $owner->id,
        ]);
    }

    public function test_自分の読書計画を読了にできる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => today()->addDays(7),
            'status' => ReadingPlanStatus::InProgress,
            'completed_at' => null,
        ]);

        $response = $this
            ->actingAs($user)
            ->post("/reading-plans/{$readingPlan->id}/complete");

        $response->assertRedirect(
            route('reading-plans.index')
        );

        $readingPlan->refresh();

        $this->assertSame(
            ReadingPlanStatus::Completed,
            $readingPlan->status
        );

        $this->assertNotNull(
            $readingPlan->completed_at
        );
    }

    public function test_他人の読書計画は読了にできない(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create();

        $readingPlan = ReadingPlan::create([
            'user_id' => $owner->id,
            'book_id' => $book->id,
            'target_date' => today()->addDays(7),
            'status' => ReadingPlanStatus::InProgress,
            'completed_at' => null,
        ]);

        $response = $this
            ->actingAs($otherUser)
            ->post("/reading-plans/{$readingPlan->id}/complete");

        $response->assertStatus(403);

        $readingPlan->refresh();

        $this->assertSame(
            ReadingPlanStatus::InProgress,
            $readingPlan->status
        );

        $this->assertNull(
            $readingPlan->completed_at
        );
    }

    public function test_同じ書籍の進行中の読書計画は重複登録できない(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => now()->addDays(7)->toDateString(),
            'status' => ReadingPlanStatus::InProgress,
            'completed_at' => null,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('reading-plans.store'), [
                'book_id' => $book->id,
                'target_date' => now()->addDays(14)->toDateString(),
            ]);

        $response->assertSessionHasErrors([
            'book_id' => 'この書籍はすでに読書計画に登録されています。',
        ]);

        $this->assertDatabaseCount('reading_plans', 1);
    }

    public function test_完了済みの読書計画は編集できない(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => now()->addDays(7)->toDateString(),
            'status' => ReadingPlanStatus::Completed,
            'completed_at' => now(),
        ]);

        $response = $this
            ->actingAs($user)
            ->put(route('reading-plans.update', $readingPlan), [
                'target_date' => now()->addDays(14)->toDateString(),
            ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'target_date' => now()->addDays(7)->toDateString(),
        ]);
    }

    public function test_期限切れの読書計画は編集できない(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => now()->subDay()->toDateString(),
            'status' => ReadingPlanStatus::Expired,
            'completed_at' => null,
        ]);

        $response = $this
            ->actingAs($user)
            ->put(route('reading-plans.update', $readingPlan), [
                'target_date' => now()->addDays(7)->toDateString(),
            ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'target_date' => now()->subDay()->toDateString(),
            'status' => ReadingPlanStatus::Expired->value,
        ]);
    }
}
