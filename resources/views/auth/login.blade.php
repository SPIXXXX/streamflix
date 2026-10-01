<x-guest-layout>
    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="email" value="Email" class="!text-sf-muted" />
            <x-text-input id="email" class="block mt-1 w-full bg-sf-bg border-sf-border text-sf-text rounded-lg focus:border-sf-blue focus:ring-sf-blue" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" value="Password" class="!text-sf-muted" />
            <x-password-input id="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-sf-muted">
                <input type="checkbox" name="remember" class="rounded bg-sf-bg border-sf-border text-sf-blue focus:ring-sf-blue">
                Remember me
            </label>
            @if (Route::has('password.request'))
                <a class="text-sm text-sf-blue hover:underline" href="{{ route('password.request') }}">Forgot password?</a>
            @endif
        </div>

        <button type="submit" class="w-full bg-sf-blue hover:bg-sf-blue-dark text-white font-medium py-2.5 rounded-lg shadow-glow-blue transition">
            Log In
        </button>

        <div class="flex items-center gap-3 py-1 text-xs text-sf-muted" aria-hidden="true">
            <span class="h-px flex-1 bg-sf-border"></span>
            <span>OR CONTINUE WITH</span>
            <span class="h-px flex-1 bg-sf-border"></span>
        </div>

        <a href="{{ route('google.redirect') }}" class="flex w-full items-center justify-center gap-3 rounded-lg border border-sf-border bg-sf-surface-light px-4 py-2.5 font-medium text-sf-text transition hover:bg-sf-surface">
            <svg class="h-5 w-5" viewBox="0 0 48 48" aria-hidden="true">
                <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z" transform="translate(0 5)" />
                <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.74 7.18l7.66 5.94c4.48-4.13 7.12-10.2 7.12-17.59z" />
                <path fill="#FBBC05" d="M10.53 28.59a14.4 14.4 0 0 1 0-9.18l-7.98-6.19a23.95 23.95 0 0 0 0 21.56l7.98-6.19z" transform="translate(0 5)" />
                <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.9-5.81l-7.66-5.94c-2.13 1.43-4.86 2.28-8.24 2.28-6.26 0-11.57-4.22-13.46-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z" />
            </svg>
            Continue with Google
        </a>

        <p class="text-center text-sm text-sf-muted">
            Don't have an account?
            <a href="{{ route('register') }}" class="text-sf-blue hover:underline">Sign up</a>
        </p>
    </form>
</x-guest-layout>
