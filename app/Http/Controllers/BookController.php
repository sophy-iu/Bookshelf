<?php

namespace App\Http\Controllers;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookController extends Controller
{
    public function index()
    {
        $books = Book::paginate(10);

        return view('books.index', compact('books'));
    }

    public function show(Book $book)
    {
        return view('books.show', compact('book'));
    }

    public function create(Book $book)
    {
        $genres = Genre::all();

        $bookGenreIds = $book->genres->pluck('id')->toArray();

        return view('books.create', compact(
            'book',
            'genres',
            'bookGenreIds'
        ));
    }

    public function store(BookCreateRequest $request)
    {
        $validated = $request->validated();

        Auth::user()->books()->create($validated);

        return redirect()->route('books.index');
    }

    public function edit(Book $book)
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

    public function update(Request $request, Book $book)
    {
        $book->update([
            'title' => $request->title,
            
        ]);

        return redirect()
            ->route('books.show', $book)
            ->with('success', '書籍を更新しました。');
    }

    public function post(Request $request, Book $book)
    {
        $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string'],
        ]);

        Review::create([
            'user_id' => Auth::id(),
            'book_id' => $book->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return redirect()->back();
    }

    public function ranking(Book $book)
    {
        return view('ranking.index', compact('book'));
    }
}
