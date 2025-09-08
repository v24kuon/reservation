<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateInstructorProfileRequest;
use App\Models\InstructorProfile;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class InstructorProfileController extends Controller
{
    /**
     * Show the instructor's own profile edit page.
     */
    public function editSelf(): View
    {
        $user = Auth::user();

        $profile = $user->instructorProfile ?: new InstructorProfile(['user_id' => $user->id]);

        return view('instructor.profile.edit', [
            'user' => $user,
            'profile' => $profile,
        ]);
    }

    /**
     * Update the instructor's own profile.
     */
    public function updateSelf(UpdateInstructorProfileRequest $request): RedirectResponse
    {
        $user = Auth::user();

        $data = $request->validated();

        $profile = $user->instructorProfile ?: new InstructorProfile(['user_id' => $user->id]);

        if (isset($data['image'])) {
            $path = $data['image']->store('instructors', 'public');
            $data['image_path'] = $path;
            unset($data['image']);
        }

        $profile->fill($data);
        $profile->save();

        return redirect()->route('instructor.profile.edit')->with('status', 'プロフィールを更新しました');
    }

    /**
     * Show admin edit page for a specific instructor.
     */
    public function edit(User $instructor): View
    {
        $profile = $instructor->instructorProfile ?: new InstructorProfile(['user_id' => $instructor->id]);

        return view('admin.instructors.edit', [
            'user' => $instructor,
            'profile' => $profile,
        ]);
    }

    /**
     * Update admin-managed instructor profile.
     */
    public function update(UpdateInstructorProfileRequest $request, User $instructor): RedirectResponse
    {
        $data = $request->validated();

        $profile = $instructor->instructorProfile ?: new InstructorProfile(['user_id' => $instructor->id]);

        if (isset($data['image'])) {
            $path = $data['image']->store('instructors', 'public');
            $data['image_path'] = $path;
            unset($data['image']);
        }

        $profile->fill($data);
        $profile->save();

        return redirect()->route('admin.instructors.edit', $instructor)->with('status', 'プロフィールを更新しました');
    }
}
