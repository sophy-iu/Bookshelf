<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use Illuminate\Http\Request;
use App\Http\Requests\BookCreateRequest;
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

        $genreIds = $validated['genres'];
        unset($validated['genres']);

        $book = Auth::user()->books()->create($validated);

        $book->genres()->sync($genreIds);

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

    public function destroy(Book $book)
    {
        $this->authorize('delete', $book);

        $book->delete();

        return redirect()->route('books.index');
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

    public function ranking(Book $book)
    {
        return view('ranking.index', compact('book'));
    }
}
