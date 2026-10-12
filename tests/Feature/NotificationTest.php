<?php

namespace Tests\Feature;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use App\Notifications\ReadingPlanReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_通知一覧には自分の通知だけが表示される(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $book = Book::factory()->create();

        $myReadingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => today()->addDays(3),
            'status' => ReadingPlanStatus::InProgress,
            'completed_at' => null,
        ]);

        $otherReadingPlan = ReadingPlan::create([
            'user_id' => $otherUser->id,
            'book_id' => $book->id,
            'target_date' => today()->addDays(3),
            'status' => ReadingPlanStatus::InProgress,
            'completed_at' => null,
        ]);

        $user->notify(
            new ReadingPlanReminderNotification(
                $myReadingPlan,
                'three_days_before'
            )
        );

        $otherUser->notify(
            new ReadingPlanReminderNotification(
                $otherReadingPlan,
                'three_days_before'
            )
        );

        $response = $this
            ->actingAs($user)
            ->get('/notifications');

        $response->assertStatus(200);

        $response->assertSee(
            '「'.$book->title.'」の読書目標日まであと3日です。'
        );

        $this->assertCount(
            1,
            $user->notifications
        );
    }

    public function test_自分の通知を既読にできる(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'title' => 'Laravel入門',
        ]);

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => today()->addDays(3),
            'status' => ReadingPlanStatus::InProgress,
            'completed_at' => null,
        ]);

        $user->notify(
            new ReadingPlanReminderNotification(
                $readingPlan,
                'three_days_before'
            )
        );

        $notification = $user->notifications()->first();

        // 既読にする前は null
        $this->assertNull($notification->read_at);

        $response = $this
            ->actingAs($user)
            ->post("/notifications/{$notification->id}/read");

        $response->assertRedirect(
            route('notifications.index')
        );

        // DBから最新状態を取得
        $notification->refresh();

        // 既読日時が設定されたことを確認
        $this->assertNotNull(
            $notification->read_at
        );
    }

    public function test_他人の通知は既読にできない(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $book = Book::factory()->create([
            'title' => 'Laravel入門',
        ]);

        $readingPlan = ReadingPlan::create([
            'user_id' => $owner->id,
            'book_id' => $book->id,
            'target_date' => today()->addDays(3),
            'status' => ReadingPlanStatus::InProgress,
            'completed_at' => null,
        ]);

        $owner->notify(
            new ReadingPlanReminderNotification(
                $readingPlan,
                'three_days_before'
            )
        );

        $notification = $owner->notifications()->first();

        $response = $this
            ->actingAs($otherUser)
            ->post("/notifications/{$notification->id}/read");

        $response->assertStatus(404);

        $notification->refresh();

        $this->assertNull(
            $notification->read_at
        );
    }

    public function test_未ログインユーザーは通知一覧にアクセスできない(): void
    {
        $response = $this->get('/notifications');

        $response->assertRedirect('/login');
    }

    public function test_未ログインユーザーは通知を既読にできない(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'title' => 'Laravel入門',
        ]);

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => today()->addDays(3),
            'status' => ReadingPlanStatus::InProgress,
            'completed_at' => null,
        ]);

        $user->notify(
            new ReadingPlanReminderNotification(
                $readingPlan,
                'three_days_before'
            )
        );

        $notification = $user->notifications()->first();

        $response = $this->post(
            "/notifications/{$notification->id}/read"
        );

        $response->assertRedirect('/login');

        $notification->refresh();

        $this->assertNull(
            $notification->read_at
        );
    }
}
