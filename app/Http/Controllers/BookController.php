<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookCreateRequest;
use App\Http\Requests\BookUpdateRequest;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class BookController extends Controller
{
    /**
     * 書籍一覧を検索・絞り込み・並び替えて表示する。
     */
    public function index(Request $request): View
    {
        $query = Book::query()
            ->with('genres')
            ->withAvg('reviews', 'rating');

        // キーワード検索
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');

            $query->where(function ($query) use ($keyword) {
                $query->where('title', 'like', "%{$keyword}%")
                    ->orWhere('author', 'like', "%{$keyword}%");
            });
        }

        // ジャンル絞り込み
        if ($request->filled('genre')) {
            $genreId = $request->input('genre');

            $query->whereHas('genres', function ($query) use ($genreId) {
                $query->where('genres.id', $genreId);
            });
        }

        // 並び替え
        switch ($request->input('sort', 'newest')) {
            case 'oldest':
                $query->orderBy('created_at');
                break;

            case 'rating':
                $query->orderByDesc('reviews_avg_rating');
                break;

            case 'title':
                $query->orderBy('title');
                break;

            default:
                $query->orderByDesc('created_at');
                break;
        }

        $books = $query
            ->paginate(10)
            ->withQueryString();

        $genres = Genre::all();

        return view('books.index', compact('books', 'genres'));
    }

    /**
     * 書籍詳細を表示する。
     */
    public function show(Book $book): View
    {
        return view('books.show', compact('book'));
    }

    /**
     * 書籍登録画面を表示する。
     */
    public function create(Book $book): View
    {
        $genres = Genre::all();

        $bookGenreIds = $book->genres->pluck('id')->toArray();

        return view('books.create', compact(
            'book',
            'genres',
            'bookGenreIds'
        ));
    }

    /**
     * 書籍を登録する。
     */
    public function store(BookCreateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $genreIds = $validated['genres'];
        unset($validated['genres']);

        $book = DB::transaction(function () use ($validated, $genreIds) {
            // 書籍を登録する
            $book = Auth::user()->books()->create($validated);

            // ジャンルを紐付ける
            $book->genres()->sync($genreIds);

            return $book;
        });

        return redirect()->route('books.index');
    }

    /**
     * 書籍編集画面を表示する。
     */
    public function edit(Book $book): View
    {
        $this->authorize('update', $book);

        $genres = Genre::all();

        $bookGenreIds = $book->genres->pluck('id')->toArray();

        return view('books.edit', compact(
            'book',
            'genres',
            'bookGenreIds'
        ));
    }

    /**
     * 指定された書籍を削除する。
     */
    public function destroy(Book $book): RedirectResponse
    {
        $this->authorize('delete', $book);

        $book->delete();

        return redirect()->route('books.index');
    }

    /**
     * 指定された書籍を更新する。
     */
    public function update(
        BookUpdateRequest $request,
        Book $book
    ): RedirectResponse {
        $this->authorize('update', $book);

        $validated = $request->validated();

        $genreIds = $validated['genres'];
        unset($validated['genres']);

        DB::transaction(function () use ($book, $validated, $genreIds) {
            $book->update($validated);

            $book->genres()->sync($genreIds);
        });

        return redirect()->route('books.show', $book);
    }

    /**
     * 書籍のランキングを表示する。
     */
    public function ranking(): View
    {
        $rankedBooks = Book::whereHas('reviews')
            ->withAvg('reviews', 'rating')
            ->orderByDesc('reviews_avg_rating')
            ->take(10)
            ->get();

        return view('ranking.index', compact('rankedBooks'));
    }

    /**
     * ISBNから書籍情報を検索する。
     */
    public function searchByIsbn(string $isbn): JsonResponse
    {
        $response = Http::get('https://www.googleapis.com/books/v1/volumes', [
            'q' => 'isbn:'.$isbn,
            'key' => config('services.google_books.api_key'),
        ]);

        if ($response->failed()) {
            return response()->json([
                'message' => 'Google Books APIの取得に失敗しました。',
            ], 500);
        }

        $data = $response->json();

        if (empty($data['items'])) {
            return response()->json([
                'message' => '書籍が見つかりませんでした。',
            ], 404);
        }

        $volumeInfo = $data['items'][0]['volumeInfo'];

        return response()->json([
            'title' => $volumeInfo['title'] ?? null,
            'author' => $volumeInfo['authors'][0] ?? null,
            'published_date' => $volumeInfo['publishedDate'] ?? null,
            'description' => $volumeInfo['description'] ?? null,
            'image_url' => $volumeInfo['imageLinks']['thumbnail'] ?? null,
        ]);
    }
}
