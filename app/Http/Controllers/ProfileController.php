<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
        unset($validated['avatar']);

        $request->user()->fill($validated);

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        if ($avatar) {
            if ($request->user()->avatar_path && ! Str::startsWith($request->user()->avatar_path, 'https://')) {
                Storage::disk('public')->delete($request->user()->avatar_path);
            }

            $request->user()->avatar_path = $avatar->store('avatars', 'public');
        }

        $request->user()->save();

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
