<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    /**
     * お気に入り書籍一覧を表示する。
     */
    public function index(): View
    {
        $books = auth()->user()
            ->favoritebooks()
            ->paginate(10);

        return view('favorites.index', compact('books'));
    }

    /**
     * 指定された書籍のお気に入り状態を切り替える。
     */
    public function toggle(Book $book): RedirectResponse
    {
        $user = auth()->user();

        $user->favoritebooks()->toggle($book->id);

        return redirect()->back();
    }
}
