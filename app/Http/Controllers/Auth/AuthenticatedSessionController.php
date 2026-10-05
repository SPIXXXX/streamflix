<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $status = Auth::user()->status ?? null;

        if (in_array($status, ['suspended', 'banned'], true)) {
            Auth::logout();

            return back()->withErrors([
                'email' => 'Your account has been ' . $status . '. Contact support if you believe this is an error.',
            ]);
        }

        $request->session()->regenerate();

        if (Auth::user()->hasRole('admin')) {
            return redirect()->intended(route('admin.dashboard', absolute: false));
        }

        return redirect()->route('films.index');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->user()?->forceFill(['last_logged_out_at' => now()])->save();

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
