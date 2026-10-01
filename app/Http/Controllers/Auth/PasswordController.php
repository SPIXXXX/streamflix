<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $googleReauthenticated = (int) $request->session()->get('google_reauthenticated_user_id') === $request->user()->id
            && $request->session()->get('google_reauthenticated_at', 0) >= now()->subMinutes(5)->timestamp;

        $rules = [
            'password' => ['required', Password::defaults(), 'confirmed'],
        ];

        if ($googleReauthenticated) {
            $rules['current_password'] = ['nullable', 'current_password'];
        } else {
            $rules['current_password'] = ['required', 'current_password'];
        }

        $validated = $request->validateWithBag('updatePassword', $rules);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $request->session()->forget(['google_reauthenticated_user_id', 'google_reauthenticated_at']);

        return back()->with('status', 'password-updated');
    }
}
