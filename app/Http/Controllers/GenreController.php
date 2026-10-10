<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use App\Models\Book;
use App\Http\Requests\GenreCreateRequest;
use App\Http\Requests\GenreUpdateRequest;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    public function index()
    {
        $genres = Genre::withCount('books')->get();

        return view('genres.index', compact('genres'));
    }

    public function store(GenreCreateRequest $request)
    {
        Genre::create($request->validated());

        return redirect()
            ->route('genres.index')
            ->with('success', 'ジャンルを登録しました');
    }

    public function show(Genre $genre)
    {
        $books = $genre->books()
            ->paginate(10);

        return view('genres.show', compact('genre', 'books'));
    }

    public function create()
    {
        return view('genres.create');
    }

    public function edit(Genre $genre)
    {
        return view('genres.edit', compact('genre'));
    }

    public function destroy(Genre $genre)
    {
        $genre->delete();

        return redirect()
            ->route('genres.index')
            ->with('success', 'ジャンルを削除しました');
    }

    public function update(GenreUpdateRequest $request, Genre $genre)
    {
        $genre->update($request->validated());

        return redirect()
            ->route('genres.index')
            ->with('success', 'ジャンルを更新しました');
    }
}
