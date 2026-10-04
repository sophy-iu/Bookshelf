<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Book;
use App\Http\Requests\ReviewUpdateRequest;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(ReviewCreateRequest $request, Book $book)
    {
        Review::create([
            'user_id' => Auth::id(),
            'book_id' => $book->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return redirect()->back();
    }

    public function toggleLike(Review $review)
    {
        $user = auth()->user();

        $user->likedReviews()->toggle($review->id);

        return redirect()->back();
    }

    public function edit(Review $review)
    {
        $this->authorize('update', $review);

        return view('reviews.edit', compact('review'));
    }

    public function destroy(Review $review)
    {
        $this->authorize('delete', $review);

        $review->delete();

        return redirect()->route('books.show', $review->book_id);
    }

    public function update(ReviewUpdateRequest $request, Review $review)
    {
        $this->authorize('update', $review);

        $review->update($request->validated());

        return redirect()->route('books.show', $review->book_id);
    }
}
