<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLessonRequest;
use App\Http\Requests\UpdateLessonRequest;
use App\Models\Lesson;
use App\Models\LessonCategory;
use App\Models\Store;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;

class LessonController extends Controller
{
    // Note: Controller base class does not include AuthorizesRequests here; route middleware already protects admin.
    public function index(): View
    {
        $lessons = Lesson::query()
            ->with(['store:id,name', 'category:id,name', 'instructor:id,name'])
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view('admin.lessons.index', compact('lessons'));
    }

    public function create(): View
    {
        $stores = Store::query()->active()->orderBy('name')->get(['id', 'name']);
        $categories = LessonCategory::query()->whereNotNull('parent_id')->active()->orderBy('sort_order')->get(['id', 'name']);
        $instructors = User::query()->where('role', User::ROLE_INSTRUCTOR)->orderBy('name')->get(['id', 'name']);

        return view('admin.lessons.create', compact('stores', 'categories', 'instructors'));
    }

    public function store(StoreLessonRequest $request): RedirectResponse
    {
        Lesson::query()->create($request->validated());

        return redirect()->route('admin.lessons.index')->with('status', 'レッスンを作成しました');
    }

    public function show(Lesson $lesson): View
    {
        $lesson->load(['store', 'category', 'instructor', 'schedules']);

        return view('admin.lessons.show', compact('lesson'));
    }

    public function edit(Lesson $lesson): View
    {
        $stores = Store::query()->active()->orderBy('name')->get(['id', 'name']);
        $categories = LessonCategory::query()->whereNotNull('parent_id')->active()->orderBy('sort_order')->get(['id', 'name']);
        $instructors = User::query()->where('role', User::ROLE_INSTRUCTOR)->orderBy('name')->get(['id', 'name']);

        return view('admin.lessons.edit', compact('lesson', 'stores', 'categories', 'instructors'));
    }

    public function update(UpdateLessonRequest $request, Lesson $lesson): RedirectResponse
    {
        $lesson->update($request->validated());

        return redirect()->route('admin.lessons.index')->with('status', 'レッスンを更新しました');
    }

    public function destroy(Lesson $lesson): RedirectResponse
    {
        try {
            $lesson->delete();
        } catch (QueryException $e) {
            return redirect()->route('admin.lessons.index')
                ->with('status', '関連データが存在するため削除できませんでした');
        }

        return redirect()->route('admin.lessons.index')->with('status', 'レッスンを削除しました');
    }
}
