<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenreCreateRequest;
use App\Http\Requests\GenreUpdateRequest;
use App\Models\Genre;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GenreController extends Controller
{
    /**
     * ジャンル一覧を表示する。
     */
    public function index(): View
    {
        $genres = Genre::withCount('books')->get();

        return view('genres.index', compact('genres'));
    }

    /**
     * ジャンルを登録する。
     */
    public function store(GenreCreateRequest $request): RedirectResponse
    {
        Genre::create($request->validated());

        return redirect()
            ->route('genres.index')
            ->with('success', 'ジャンルを登録しました');
    }

    /**
     * 指定されたジャンルの書籍一覧を表示する。
     */
    public function show(Genre $genre): View
    {
        $books = $genre->books()
            ->paginate(10);

        return view('genres.show', compact('genre', 'books'));
    }

    /**
     * ジャンル登録画面を表示する。
     */
    public function create(): View
    {
        return view('genres.create');
    }

    /**
     * ジャンル編集画面を表示する。
     */
    public function edit(Genre $genre): View
    {
        return view('genres.edit', compact('genre'));
    }

    /**
     * 指定されたジャンルを削除する。
     */
    public function destroy(Genre $genre): RedirectResponse
    {
        $genre->delete();

        return redirect()
            ->route('genres.index')
            ->with('success', 'ジャンルを削除しました');
    }

    /**
     * 指定されたジャンルを更新する。
     */
    public function update(
        GenreUpdateRequest $request,
        Genre $genre
    ): RedirectResponse {
        $genre->update($request->validated());

        return redirect()
            ->route('genres.index')
            ->with('success', 'ジャンルを更新しました');
    }
}