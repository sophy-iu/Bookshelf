<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewCreateRequest;
use App\Http\Requests\ReviewUpdateRequest;
use App\Models\Book;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReviewController extends Controller
{
    /**
     * 指定された書籍にレビューを登録する。
     */
    public function store(
        ReviewCreateRequest $request,
        Book $book
    ): RedirectResponse {
        Review::create([
            'user_id' => Auth::id(),
            'book_id' => $book->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return redirect()->back();
    }

    /**
     * 指定されたレビューのいいね状態を切り替える。
     */
    public function toggleLike(Review $review): RedirectResponse
    {
        $user = auth()->user();

        $user->likedReviews()->toggle($review->id);

        return redirect()->back();
    }

    /**
     * レビュー編集画面を表示する。
     */
    public function edit(Review $review): View
    {
        $this->authorize('update', $review);

        return view('reviews.edit', compact('review'));
    }

    /**
     * 指定されたレビューを削除する。
     */
    public function destroy(Review $review): RedirectResponse
    {
        $this->authorize('delete', $review);

        $review->delete();

        return redirect()->route('books.show', $review->book_id);
    }

    /**
     * 指定されたレビューを更新する。
     */
    public function update(
        ReviewUpdateRequest $request,
        Review $review
    ): RedirectResponse {
        $this->authorize('update', $review);

        $review->update($request->validated());

        return redirect()->route('books.show', $review->book_id);
    }
}