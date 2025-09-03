<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLessonCategoryRequest;
use App\Http\Requests\UpdateLessonCategoryRequest;
use App\Models\LessonCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class LessonCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $categories = LessonCategory::query()
            ->with('parent')
            ->orderBy('sort_order')
            ->latest('id')
            ->paginate(15);

        return view('admin.lesson_categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $parents = LessonCategory::query()->whereNull('parent_id')->orderBy('sort_order')->get(['id', 'name']);

        return view('admin.lesson_categories.create', compact('parents'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLessonCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        LessonCategory::query()->create($data);

        return redirect()->route('admin.lesson-categories.index')->with('status', 'カテゴリを作成しました');
    }

    /**
     * Display the specified resource.
     */
    public function show(LessonCategory $lesson_category): View
    {
        $lesson_category->load('parent', 'children');

        return view('admin.lesson_categories.show', ['category' => $lesson_category]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LessonCategory $lesson_category): View
    {
        $parents = LessonCategory::query()
            ->whereNull('parent_id')
            ->where('id', '!=', $lesson_category->id)
            ->orderBy('sort_order')
            ->get(['id', 'name']);

        return view('admin.lesson_categories.edit', [
            'category' => $lesson_category,
            'parents' => $parents,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLessonCategoryRequest $request, LessonCategory $lesson_category): RedirectResponse
    {
        $data = $request->validated();
        $lesson_category->update($data);

        return redirect()->route('admin.lesson-categories.index')->with('status', 'カテゴリを更新しました');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LessonCategory $lesson_category): RedirectResponse
    {
        $lesson_category->delete();

        return redirect()->route('admin.lesson-categories.index')->with('status', 'カテゴリを削除しました');
    }
}
