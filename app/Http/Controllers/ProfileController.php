<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display user's profile dashboard (subscriptions, reservations, quick links).
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // Show only up to 3 latest subscriptions for the dashboard
        $subscriptions = $user->userSubscriptions()
            ->with('plan')
            ->orderByDesc('current_period_end')
            ->limit(3)
            ->get();

        // Show only up to 3 recent reservations for the dashboard
        $reservations = $user->reservations()
            ->with(['lessonSchedule.lesson.store'])
            ->orderByDesc('created_at')
            ->limit(3)
            ->get();

        // Fetch favorite stores
        $favoriteStores = $user->favorites()
            ->stores()
            ->with('favoritable')
            ->latest()
            ->take(6)
            ->get()
            ->pluck('favoritable')
            ->filter();

        // Fetch favorite instructors
        $favoriteInstructors = $user->favorites()
            ->instructors()
            ->with('favoritable.instructorProfile')
            ->latest()
            ->take(6)
            ->get()
            ->pluck('favoritable')
            ->filter();

        // Cancel flags are now persisted via webhooks; no runtime Stripe calls required

        return view('profile.index', compact('user', 'subscriptions', 'reservations', 'favoriteStores', 'favoriteInstructors'));
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
