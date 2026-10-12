<?php

namespace App\Http\Controllers;

use App\Models\ReadingPlan;
use Illuminate\View\View;
use App\Enums\ReadingPlanStatus;
use App\Http\Requests\StoreReadingPlanRequest;
use App\Models\Book;
use Illuminate\Http\RedirectResponse;

class ReadingPlanController extends Controller
{
    /**
     * 自分の読書計画一覧を表示する
     */
    public function index(): View
    {
        $readingPlans = ReadingPlan::with('book')
            ->where('user_id', auth()->id())
            ->orderBy('target_date')
            ->get();

        return view('reading-plans.index', compact('readingPlans'));
    }

    /**
     * 読書計画登録画面を表示する
     */
    public function create(): View
    {
        $books = Book::orderBy('title')->get();

        return view('reading-plans.create', compact('books'));
    }

    /**
     * 読書計画を登録する
     */
    public function store(StoreReadingPlanRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        ReadingPlan::create([
            'user_id' => auth()->id(),
            'book_id' => $validated['book_id'],
            'target_date' => $validated['target_date'],
            'status' => ReadingPlanStatus::IN_PROGRESS,
            'completed_at' => null,
        ]);

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を登録しました。');
    }

    /**
     * 読書計画編集画面を表示する
     */
    public function edit(ReadingPlan $readingPlan): View
    {
        $this->authorize('update', $readingPlan);

        return view('reading-plans.edit', compact('readingPlan'));
    }

    /**
     * 読書計画を更新する
     */
    public function update(
        UpdateReadingPlanRequest $request,
        ReadingPlan $readingPlan
    ): RedirectResponse {
        $this->authorize('update', $readingPlan);

        $validated = $request->validated();

        $readingPlan->update([
            'target_date' => $validated['target_date'],
        ]);

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を更新しました。');
    }

    /**
     * 読書計画を削除する
     */
    public function destroy(ReadingPlan $readingPlan): RedirectResponse
    {
        $this->authorize('delete', $readingPlan);

        $readingPlan->delete();

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を削除しました。');
    }

    /**
     * 読書計画を読了にする
     */
    public function complete(ReadingPlan $readingPlan): RedirectResponse
    {
        $this->authorize('update', $readingPlan);

        $readingPlan->update([
            'status' => ReadingPlanStatus::COMPLETED,
            'completed_at' => now(),
        ]);

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読了にしました。');
    }
}