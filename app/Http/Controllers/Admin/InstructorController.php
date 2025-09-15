<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InstructorStoreRequest;
use App\Http\Requests\Admin\InstructorUpdateRequest;
use App\Models\InstructorProfile;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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

    public function show(User $instructor): View
    {
        abort_unless($instructor->role === 'instructor', 404);
        $this->authorize('view', $instructor);
        $profile = $instructor->instructorProfile ?: new InstructorProfile(['user_id' => $instructor->id]);

        return view('admin.instructors.show', [
            'instructor' => $instructor,
            'profile' => $profile,
        ]);
    }

    public function update(InstructorUpdateRequest $request, User $instructor): RedirectResponse
    {
        abort_unless($instructor->role === 'instructor', 404);
        $validated = $request->validated();
        $attributes = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'] ?? null,
        ];
        $profileData = [
            'image' => $validated['image'] ?? null,
            'remove_image' => $validated['remove_image'] ?? null,
            'bio' => $validated['bio'] ?? null,
            'qualifications' => $validated['qualifications'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ];

        $oldPath = $instructor->instructorProfile?->image_path;
        $newPath = null;
        $removeOld = ! empty($profileData['remove_image']);
        unset($profileData['remove_image']);

        try {
            DB::transaction(function () use ($instructor, $attributes, &$profileData, &$newPath, $removeOld) {
                // Update user basic info
                $instructor->name = $attributes['name'];
                $instructor->email = $attributes['email'];
                if (! empty($attributes['password'])) {
                    $instructor->password = Hash::make($attributes['password']);
                }
                $instructor->role = 'instructor';
                $instructor->save();

                // Prepare profile
                $profile = $instructor->instructorProfile ?: new InstructorProfile(['user_id' => $instructor->id]);

                if (isset($profileData['image'])) {
                    $path = $profileData['image']->store('instructors', 'public');
                    $profileData['image_path'] = $path;
                    $newPath = $path;
                    unset($profileData['image']);
                }

                unset($profileData['user_id']);
                if ($removeOld) {
                    $profile->image_path = null;
                }
                $profile->fill(\Illuminate\Support\Arr::only($profileData, ['image_path', 'bio', 'qualifications', 'notes']));
                if (! $profile->user_id) {
                    $profile->user_id = $instructor->id;
                }
                $profile->save();
            });
        } catch (\Throwable $e) {
            if (! empty($newPath)) {
                Storage::disk('public')->delete($newPath);
            }
            throw $e;
        }

        // Cleanup old file if removed or replaced (after commit)
        if (($removeOld && ! empty($oldPath)) || (! empty($newPath) && ! empty($oldPath) && $oldPath !== $newPath)) {
            DB::afterCommit(fn () => Storage::disk('public')->delete($oldPath));
        }

        return redirect()->route('admin.instructors.index')->with('status', 'インストラクターを更新しました。');
    }

    public function destroy(User $instructor): RedirectResponse
    {
        abort_unless($instructor->role === 'instructor', 404);
        if ($instructor->instructorProfile && $instructor->instructorProfile->image_path) {
            Storage::disk('public')->delete($instructor->instructorProfile->image_path);
        }
        $instructor->delete();

        return redirect()->route('admin.instructors.index')->with('status', 'インストラクターを削除しました。');
    }
}
