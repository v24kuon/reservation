<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateInstructorProfileRequest;
use App\Models\InstructorProfile;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class InstructorProfileController extends Controller
{
    public function __construct()
    {
        // Ensure only authenticated users can access self-profile endpoints
        $this->middleware('auth')->only(['editSelf', 'updateSelf']);
    }

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

        // Reject unexpected user_id field just in case
        unset($data['user_id']);

        // Remove existing image when requested
        if (! empty($data['remove_image']) && $profile->image_path) {
            Storage::disk('public')->delete($profile->image_path);
            $profile->image_path = null;
        }

        $oldPath = $profile->image_path;
        $newPath = null;
        if (isset($data['image'])) {
            $path = $data['image']->store('instructors', 'public');
            $data['image_path'] = $path;
            $newPath = $path;
            unset($data['image']);
        }

        $profile->fill($data);
        // Ensure user_id is set on first creation
        if (! $profile->user_id) {
            $profile->user_id = $user->id;
        }
        $profile->save();

        // If image was replaced, cleanup the old file
        if (! empty($newPath) && ! empty($oldPath) && $oldPath !== $newPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return redirect()->route('instructor.profile.edit')->with('status', 'プロフィールを更新しました');
    }

    // Admin edit/update are handled by Admin\InstructorController. Methods removed as unused.
}
