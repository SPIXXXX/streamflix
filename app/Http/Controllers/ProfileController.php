<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\PublicMediaStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $lists = $user->movieLists()->withCount('films')->latest()->get();
        $reviews = $user->reviews()->with('film')->latest()->get();

        return view($user->hasRole('admin') ? 'admin.profile.edit' : 'profile.edit', [
            'user' => $user,
            'lists' => $lists,
            'reviews' => $reviews,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $avatar = $validated['avatar'] ?? null;
        $previousAvatarPath = $request->user()->avatar_path;
        unset($validated['avatar']);

        $request->user()->fill($validated);

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        if ($avatar) {
            $request->user()->avatar_path = app(PublicMediaStorage::class)->store($avatar, 'avatars');
        }

        $request->user()->save();

        if ($avatar) {
            app(PublicMediaStorage::class)->delete($previousAvatarPath);
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $googleReauthenticated = (int) $request->session()->get('google_reauthenticated_user_id') === $request->user()->id
            && $request->session()->get('google_reauthenticated_at', 0) >= now()->subMinutes(5)->timestamp;

        if (! $googleReauthenticated) {
            $request->validateWithBag('userDeletion', [
                'password' => ['required', 'current_password'],
            ]);
        }

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/')->with('success', 'Your account has been deleted.');
    }
}
