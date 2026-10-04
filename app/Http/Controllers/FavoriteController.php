<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;

class FavoriteController extends Controller
{
    public function index()
    {
        $books = auth()->user()
            ->favoritebooks()
            ->paginate(10);

        return view('favorites.index', compact('books'));
    }

    public function toggle(Book $book)
    {
        $user = auth()->user();

        $user->favoritebooks()->toggle($book->id);

        return redirect()->back();
    }
}
