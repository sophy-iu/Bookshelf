<?php

namespace App\Http\Controllers;
use App\Models\Book;

use Illuminate\Http\Request;

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

    public function create()
    {
        
        return view('books.create');
    }

    public function store(BookCreateRequest $request)
    {
        $validated = $request->validated();

        Auth::user()->books()->create($validated);

        return redirect()->route('books.index');
    }

    public function ranking()
    {
        return view('books.ranking', compact('books'));
    }
}
