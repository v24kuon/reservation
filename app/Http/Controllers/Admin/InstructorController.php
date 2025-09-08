<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InstructorStoreRequest;
use App\Http\Requests\UpdateInstructorProfileRequest;
use App\Models\InstructorProfile;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;

class InstructorController extends Controller
{
    public function index(): View
    {
        $instructors = User::query()
            ->where('role', 'instructor')
            ->latest('id')
            ->paginate(15);

        return view('admin.instructors.index', compact('instructors'));
    }

    public function create(): View
    {
        return view('admin.instructors.create');
    }

    public function store(InstructorStoreRequest $request): RedirectResponse
    {
        $attributes = $request->validated();
        $user = new User;
        $user->name = $attributes['name'];
        $user->email = $attributes['email'];
        $user->password = Hash::make($attributes['password']);
        $user->role = 'instructor';
        $user->save();

        return redirect()->route('admin.instructors.index')->with('status', 'インストラクターを作成しました。');
    }

    public function edit(User $instructor): View
    {
        abort_unless($instructor->role === 'instructor', 404);
        $profile = $instructor->instructorProfile ?: new InstructorProfile(['user_id' => $instructor->id]);

        return view('admin.instructors.edit', [
            'instructor' => $instructor,
            'profile' => $profile,
        ]);
    }

    public function update(InstructorStoreRequest $request, UpdateInstructorProfileRequest $profileRequest, User $instructor): RedirectResponse
    {
        abort_unless($instructor->role === 'instructor', 404);
        $attributes = $request->validated();
        $profileData = $profileRequest->validated();

        $instructor->name = $attributes['name'];
        $instructor->email = $attributes['email'];
        if (! empty($attributes['password'])) {
            $instructor->password = Hash::make($attributes['password']);
        }
        $instructor->role = 'instructor';
        $instructor->save();

        // Update or create instructor profile (image, bio, qualifications, notes)
        $profile = $instructor->instructorProfile ?: new InstructorProfile(['user_id' => $instructor->id]);
        if (isset($profileData['image'])) {
            $path = $profileData['image']->store('instructors', 'public');
            $profileData['image_path'] = $path;
            unset($profileData['image']);
        }
        $profile->fill($profileData);
        $profile->save();

        return redirect()->route('admin.instructors.index')->with('status', 'インストラクターを更新しました。');
    }

    public function destroy(User $instructor): RedirectResponse
    {
        abort_unless($instructor->role === 'instructor', 404);
        $instructor->delete();

        return redirect()->route('admin.instructors.index')->with('status', 'インストラクターを削除しました。');
    }
}
