<?php

namespace App\Policies;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use App\Models\User;

class ReadingPlanPolicy
{
    /**
     * 一覧表示
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * 詳細表示
     */
    public function view(User $user, ReadingPlan $readingPlan): bool
    {
        return $user->id === $readingPlan->user_id;
    }

    /**
     * 登録
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * 更新
     */
    public function update(User $user, ReadingPlan $readingPlan): bool
    {
        return $user->id === $readingPlan->user_id
            && $readingPlan->status === ReadingPlanStatus::InProgress;
    }

    /**
     * 削除
     */
    public function delete(User $user, ReadingPlan $readingPlan): bool
    {
        return $user->id === $readingPlan->user_id;
    }

    /**
     * 復元
     */
    public function restore(User $user, ReadingPlan $readingPlan): bool
    {
        return false;
    }

    /**
     * 完全削除
     */
    public function forceDelete(User $user, ReadingPlan $readingPlan): bool
    {
        return false;
    }
}
