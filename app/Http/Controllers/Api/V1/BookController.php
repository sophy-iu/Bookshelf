<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\BookIndexRequest;
use App\Http\Requests\Api\V1\BookStoreRequest;
use App\Http\Requests\Api\V1\BookUpdateRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    /**
     * リソースの一覧を表示する。
     */
    public function index(BookIndexRequest $request)
    {
        $query = Book::with('genres')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews');

        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');

            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('author', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('genre_id')) {
            $genreId = $request->input('genre_id');

            $query->whereHas('genres', function ($q) use ($genreId) {
                $q->where('genres.id', $genreId);
            });
        }

        $perPage = $request->input('per_page', 10);

        $books = $query->paginate($perPage);

        return BookResource::collection($books);
    }

    /**
     * 新しく作成されたリソースをストレージに保存する。
     */
    public function store(BookStoreRequest $request)
    {
        $validated = $request->validated();

        $genreIds = $validated['genres'];
        unset($validated['genres']);

        $validated['user_id'] = $request->user()->id;

        $book = DB::transaction(function () use ($validated, $genreIds) {
            $book = Book::create($validated);

            $book->genres()->attach($genreIds);

            return $book;
        });

        $book->load('genres')
            ->loadAvg('reviews', 'rating')
            ->loadCount('reviews');

        return (new BookResource($book))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * 指定されたリソースを表示する。
     */
    public function show(Book $book)
    {
        $book->load([
            'genres',
            'reviews.user',
        ]);

        return new BookResource($book);
    }

    /**
     * ストレージ内の指定されたリソースを更新する。
     */
    public function update(BookUpdateRequest $request, Book $book)
    {
        $this->authorize('update', $book);

        $validated = $request->validated();

        $genreIds = $validated['genres'];
        unset($validated['genres']);

        DB::transaction(function () use ($book, $validated, $genreIds) {
            $book->update($validated);

            $book->genres()->sync($genreIds);
        });

        $book->load('genres')
            ->loadAvg('reviews', 'rating')
            ->loadCount('reviews');

        return new BookResource($book);
    }

    /**
     * ストレージから指定されたリソースを削除する。
     */
    public function destroy(Book $book)
    {
        $this->authorize('delete', $book);

        $book->delete();

        return response()->noContent();
    }
}
