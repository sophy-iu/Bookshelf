<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\User;

class BookPolicy
{
    /**
     * 書籍一覧を閲覧できるか判定する。
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * 指定された書籍を閲覧できるか判定する。
     */
    public function view(User $user, Book $book): bool
    {
        return true;
    }

    /**
     * 書籍を登録できるか判定する。
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * 指定された書籍を更新できるか判定する。
     */
    public function update(User $user, Book $book): bool
    {
        return $user->id === $book->user_id;
    }

    /**
     * 指定された書籍を削除できるか判定する。
     */
    public function delete(User $user, Book $book): bool
    {
        return $user->id === $book->user_id;
    }

    /**
     * 指定された書籍を復元できるか判定する。
     */
    public function restore(User $user, Book $book): bool
    {
        return false;
    }

    /**
     * 指定された書籍を完全に削除できるか判定する。
     */
    public function forceDelete(User $user, Book $book): bool
    {
        return false;
    }
}
