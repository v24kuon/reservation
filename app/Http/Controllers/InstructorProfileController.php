<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateInstructorProfileRequest;
use App\Models\InstructorProfile;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InstructorProfileController extends Controller
{
    public function __construct()
    {
        // Ensure only authenticated instructors/admins can access these endpoints (defense-in-depth)
        $this->middleware(['auth', 'can:access-instructor'])->only(['editSelf', 'updateSelf']);
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

        $oldPath = $profile->image_path;
        $newPath = null;
        $shouldDeleteOld = ! empty($data['remove_image']) && ! empty($oldPath);
        unset($data['remove_image']);

        try {
            if (isset($data['image'])) {
                $newPath = $data['image']->store('instructors', 'public');
                $data['image_path'] = $newPath;
                unset($data['image']);
            }

            DB::transaction(function () use ($user, $profile, $data, $shouldDeleteOld) {
                if ($shouldDeleteOld) {
                    $profile->image_path = null;
                }
                $profile->fill(\Illuminate\Support\Arr::only($data, ['image_path', 'bio', 'qualifications', 'notes']));
                // Ensure user_id is set on first creation
                if (! $profile->user_id) {
                    $profile->user_id = $user->id;
                }
                $profile->save();
            });
        } catch (\Throwable $e) {
            if ($newPath) {
                Storage::disk('public')->delete($newPath);
            }
            throw $e;
        }

        // After commit: delete old file if removed or replaced
        if (($shouldDeleteOld && $oldPath) || ($newPath && $oldPath && $oldPath !== $newPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        return redirect()->route('instructor.profile.edit')->with('status', 'プロフィールを更新しました');
    }

    // Admin edit/update are handled by Admin\InstructorController. Methods removed as unused.
}
