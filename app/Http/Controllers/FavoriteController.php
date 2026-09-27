<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index()
    {
        return view('books.show');
    }

    public function toggle(Book $book)
    {
        Auth::user()->favoriteBooks()->toggle($book->id);

        return back();
    }
}
