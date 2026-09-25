<?php

namespace App\Http\Controllers;
use App\Models\Genre;
use App\Models\Book;

use Illuminate\Http\Request;

class GenreController extends Controller
{
    public function index()
    {
        return view('genres.index');
    }

    public function show()
    {
        $books = Book::paginate(10);

        return view('genres.show');
    }

    public function edit()
    {
        $books = Book::paginate(10);
        return view('genres.edit');
    }
}
