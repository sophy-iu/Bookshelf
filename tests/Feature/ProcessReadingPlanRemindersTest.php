<?php

namespace Tests\Feature;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use App\Notifications\ReadingPlanReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcessReadingPlanRemindersTest extends TestCase
{
    use RefreshDatabase;

    public function test_期限切れの進行中読書計画がexpiredになる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => today()->subDay(),
            'status' => ReadingPlanStatus::InProgress,
            'completed_at' => null,
        ]);

        $this->artisan('reading-plans:process-reminders')
            ->assertSuccessful();

        $readingPlan->refresh();

        $this->assertSame(
            ReadingPlanStatus::Expired,
            $readingPlan->status
        );
    }

    public function test_読了済みの読書計画は期限を過ぎてもexpiredにならない(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => today()->subDays(10),
            'status' => ReadingPlanStatus::Completed,
            'completed_at' => today()->subDays(5),
        ]);

        $this->artisan('reading-plans:process-reminders')
            ->assertSuccessful();

        $readingPlan->refresh();

        $this->assertSame(
            ReadingPlanStatus::Completed,
            $readingPlan->status
        );

        $this->assertNotNull(
            $readingPlan->completed_at
        );
    }

    public function test_目標日の3日前にリマインダー通知が作成される(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'title' => 'Laravel入門',
        ]);

        ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => today()->addDays(3),
            'status' => ReadingPlanStatus::InProgress,
            'completed_at' => null,
        ]);

        $this->artisan('reading-plans:process-reminders')
            ->assertSuccessful();

        $user->refresh();

        $this->assertCount(
            1,
            $user->notifications
        );

        $notification = $user->notifications->first();

        $this->assertSame(
            ReadingPlanReminderNotification::class,
            $notification->type
        );

        $this->assertSame(
            'three_days_before',
            $notification->data['timing']
        );
    }

    public function test_目標日当日にリマインダー通知が作成される(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'title' => 'Laravel入門',
        ]);

        ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => today(),
            'status' => ReadingPlanStatus::InProgress,
            'completed_at' => null,
        ]);

        $this->artisan('reading-plans:process-reminders')
            ->assertSuccessful();

        $user->refresh();

        $this->assertCount(
            1,
            $user->notifications
        );

        $notification = $user->notifications->first();

        $this->assertSame(
            ReadingPlanReminderNotification::class,
            $notification->type
        );

        $this->assertSame(
            'on_due_date',
            $notification->data['timing']
        );
    }

    public function test_目標日の3日後にリマインダー通知が作成される(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'title' => 'Laravel入門',
        ]);

        ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => today()->subDays(3),
            'status' => ReadingPlanStatus::InProgress,
            'completed_at' => null,
        ]);

        $this->artisan('reading-plans:process-reminders')
            ->assertSuccessful();

        $user->refresh();

        $this->assertCount(
            1,
            $user->notifications
        );

        $notification = $user->notifications->first();

        $this->assertSame(
            ReadingPlanReminderNotification::class,
            $notification->type
        );

        $this->assertSame(
            'three_days_after',
            $notification->data['timing']
        );
    }

    public function test_同じリマインダー通知は重複して作成されない(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'title' => 'Laravel入門',
        ]);

        ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => today()->addDays(3),
            'status' => ReadingPlanStatus::InProgress,
            'completed_at' => null,
        ]);

        // 1回目
        $this->artisan('reading-plans:process-reminders')
            ->assertSuccessful();

        // 2回目
        $this->artisan('reading-plans:process-reminders')
            ->assertSuccessful();

        $user->refresh();

        $this->assertCount(
            1,
            $user->notifications
        );

        $notification = $user->notifications->first();

        $this->assertSame(
            'three_days_before',
            $notification->data['timing']
        );
    }

    public function test_読了済みの読書計画にはリマインダー通知が作成されない(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'title' => 'Laravel入門',
        ]);

        ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => today()->addDays(3),
            'status' => ReadingPlanStatus::Completed,
            'completed_at' => now(),
        ]);

        $this->artisan('reading-plans:process-reminders')
            ->assertSuccessful();

        $user->refresh();

        $this->assertCount(
            0,
            $user->notifications
        );
    }
}
