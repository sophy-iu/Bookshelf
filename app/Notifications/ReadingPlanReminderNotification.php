<?php

namespace App\Notifications;

use App\Models\ReadingPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReadingPlanReminderNotification extends Notification
{
    use Queueable;

    public function __construct(
        private ReadingPlan $readingPlan,
        private string $timing
    ) {
    }

    /**
     * 通知方法
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * DBに保存する通知データ
     */
    public function toArray(object $notifiable): array
    {
        return [
            'reading_plan_id' => $this->readingPlan->id,
            'book_id' => $this->readingPlan->book_id,
            'timing' => $this->timing,
            'title' => $this->title(),
            'body' => $this->body(),
        ];
    }

    /**
     * 通知タイトル
     */
    private function title(): string
    {
        return match ($this->timing) {
            'three_days_before' => '読書目標日まであと3日です',
            'on_due_date' => '今日は読書目標日です',
            'three_days_after' => '読書目標日を3日過ぎています',
            default => '読書計画のお知らせ',
        };
    }

    /**
     * 通知本文
     */
    private function body(): string
    {
        $bookTitle = $this->readingPlan->book->title;

        return match ($this->timing) {
            'three_days_before' =>
                "「{$bookTitle}」の読書目標日まであと3日です。",

            'on_due_date' =>
                "「{$bookTitle}」は今日が読書目標日です。",

            'three_days_after' =>
                "「{$bookTitle}」の読書目標日を3日過ぎています。",

            default =>
                "「{$bookTitle}」の読書計画を確認してください。",
        };
    }
}