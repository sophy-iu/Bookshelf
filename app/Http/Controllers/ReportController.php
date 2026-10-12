<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * マイ読書レポートを表示する。
     */
    public function index(): View
    {
        $user = auth()->user();

        $ratingDistribution = collect(range(1, 5))
            ->map(function ($rating) use ($user) {
                return $user->reviews()
                    ->where('rating', $rating)
                    ->count();
            });

        $topRatedBooks = $user->reviews()
            ->with('book')
            ->where('rating', '>=', 4)
            ->orderByDesc('rating')
            ->limit(5)
            ->get()
            ->map(function ($review) {
                return [
                    'id' => $review->book->id,
                    'title' => $review->book->title,
                    'author' => $review->book->author,
                    'rating' => $review->rating,
                ];
            });

        $genreRatings = $user->reviews()
            ->with('book.genres')
            ->get()
            ->flatMap(function ($review) {
                return $review->book->genres->map(function ($genre) use ($review) {
                    return [
                        'id' => $genre->id,
                        'name' => $genre->name,
                        'rating' => $review->rating,
                    ];
                });
            })
            ->groupBy('id')
            ->map(function ($reviews) {
                return [
                    'id' => $reviews->first()['id'],
                    'name' => $reviews->first()['name'],
                    'count' => $reviews->count(),
                    'average_rating' => $reviews->avg('rating'),
                ];
            })
            ->sortByDesc('average_rating')
            ->take(5)
            ->values();

        $stats = [
            'summary' => [
                'total_reviews' => $user->reviews()->count(),

                'books_read' => $user->readingPlans()
                    ->where('status', 'completed')
                    ->count(),

                'average_rating' => $user->reviews()
                    ->avg('rating') ?? 0,
            ],

            'rating_distribution' => $ratingDistribution,

            'top_rated_books' => $topRatedBooks,

            'genre_ratings' => $genreRatings,
        ];

        return view('reports.index', compact('stats'));
    }
}
