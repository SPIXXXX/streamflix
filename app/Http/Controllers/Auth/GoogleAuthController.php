<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        if (! config('services.google.client_id') || ! config('services.google.client_secret')) {
            return redirect()->route('login')->with('error', 'Google sign-in is not configured yet.');
        }

        if ($request->user()) {
            $intent = $request->query('intent');

            if (in_array($intent, ['password', 'delete-account'], true)) {
                $request->session()->put([
                    'google_reauth_expected_user_id' => $request->user()->id,
                    'google_reauth_intent' => $intent,
                ]);
            }
        }

        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            $googleProfile = $googleUser->user;
            $email = Str::lower((string) $googleUser->getEmail());
            $googleAvatar = $googleUser->getAvatar();
            $verifiedGoogleAvatar = $googleAvatar && parse_url($googleAvatar, PHP_URL_SCHEME) === 'https'
                ? $googleAvatar
                : null;
            $emailIsVerified = filter_var(
                $googleProfile['email_verified'] ?? $googleProfile['verified_email'] ?? false,
                FILTER_VALIDATE_BOOLEAN
            );

            if (! $email || ! $emailIsVerified) {
                return redirect()->route('login')->with('error', 'Google did not provide a verified email address.');
            }

            $expectedUserId = $request->session()->pull('google_reauth_expected_user_id');
            $reauthIntent = $request->session()->pull('google_reauth_intent');
            $user = $expectedUserId
                ? User::query()->find($expectedUserId)
                : User::query()->where('email', $email)->first();

            if ($expectedUserId && (! $user || Str::lower($user->email) !== $email)) {
                return redirect()->route('profile.edit')->with('error', 'Sign in with the Google account linked to this profile.');
            }

            if (! $user) {
                $user = User::create([
                    'name' => $googleUser->getName() ?: Str::before($email, '@'),
                    'email' => $email,
                    'password' => Str::random(64),
                    'status' => 'active',
                    'avatar_path' => $verifiedGoogleAvatar,
                ]);
                $user->email_verified_at = now();
                $user->save();
            }

            if (! $user->avatar_path && $verifiedGoogleAvatar) {
                $user->avatar_path = $verifiedGoogleAvatar;
                $user->save();
            }

            if (in_array($user->status, ['suspended', 'banned'], true)) {
                return redirect()->route('login')->with('error', 'Your account has been '.$user->status.'. Contact support if you believe this is an error.');
            }

            Auth::login($user);
            $request->session()->regenerate();
            $request->session()->put([
                'google_reauthenticated_user_id' => $user->id,
                'google_reauthenticated_at' => now()->timestamp,
            ]);

            if ($expectedUserId) {
                $status = $reauthIntent === 'delete-account' ? 'google-delete-ready' : 'google-reverified';

                return redirect()->route('profile.edit')->with('status', $status);
            }

            if ($user->hasRole('admin')) {
                return redirect()->intended(route('admin.dashboard', absolute: false));
            }

            return redirect()->intended(route('dashboard', absolute: false));
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('login')->with('error', 'Google sign-in could not be completed. Please try again.');
        }
    }
}
