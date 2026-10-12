<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    /**
     * レビュー一覧を閲覧できるか判定する。
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * 指定されたレビューを閲覧できるか判定する。
     */
    public function view(User $user, Review $review): bool
    {
        return true;
    }

    /**
     * レビューを登録できるか判定する。
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * 指定されたレビューを更新できるか判定する。
     */
    public function update(User $user, Review $review): bool
    {
        return $user->id === $review->user_id;
    }

    /**
     * 指定されたレビューを削除できるか判定する。
     */
    public function delete(User $user, Review $review): bool
    {
        return $user->id === $review->user_id;
    }

    /**
     * 指定されたレビューを復元できるか判定する。
     */
    public function restore(User $user, Review $review): bool
    {
        return false;
    }

    /**
     * 指定されたレビューを完全に削除できるか判定する。
     */
    public function forceDelete(User $user, Review $review): bool
    {
        return false;
    }
}
