<?php

namespace App\Console\Commands;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use App\Notifications\ReadingPlanReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ProcessReadingPlanReminders extends Command
{
    protected $signature = 'reading-plans:process-reminders';

    protected $description = '読書計画の期限切れ処理とリマインダー通知を行う';

    /**
     * 読書計画の期限切れ処理とリマインダー通知を実行する。
     */
    public function handle(): int
    {
        $today = today();

        // 目標日の3日前
        $this->sendReminders(
            $today->copy()->addDays(3),
            'three_days_before'
        );

        // 目標日当日
        $this->sendReminders(
            $today,
            'on_due_date'
        );

        // 目標日の3日後
        $this->sendReminders(
            $today->copy()->subDays(3),
            'three_days_after'
        );

        // 期限を過ぎた読書計画を期限切れにする
        ReadingPlan::expired()->update([
            'status' => ReadingPlanStatus::Expired->value,
        ]);

        $this->info('読書計画のリマインダー処理が完了しました。');

        return self::SUCCESS;
    }

    /**
     * 指定された目標日の読書計画にリマインダー通知を送信する。
     *
     * @param  Carbon  $targetDate  通知対象となる目標日
     * @param  string  $timing  通知タイミング
     */
    private function sendReminders(
        Carbon $targetDate,
        string $timing
    ): void {
        $readingPlans = ReadingPlan::with(['user', 'book'])
            ->whereDate('target_date', $targetDate)
            ->where('status', '!=', ReadingPlanStatus::Completed->value)
            ->get();

        foreach ($readingPlans as $readingPlan) {

            // 同じ読書計画・タイミングの通知がすでに存在するか確認
            $alreadyNotified = $readingPlan->user
                ->notifications()
                ->where('type', ReadingPlanReminderNotification::class)
                ->get()
                ->contains(function ($notification) use ($readingPlan, $timing) {
                    return ($notification->data['reading_plan_id'] ?? null) === $readingPlan->id
                        && ($notification->data['timing'] ?? null) === $timing;
                });

            if ($alreadyNotified) {
                continue;
            }

            $readingPlan->user->notify(
                new ReadingPlanReminderNotification(
                    $readingPlan,
                    $timing
                )
            );
        }
    }
}
