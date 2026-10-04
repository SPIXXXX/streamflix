<section>
    @php
        $googleReauthenticated = (int) session('google_reauthenticated_user_id') === auth()->id()
            && session('google_reauthenticated_at', 0) >= now()->subMinutes(5)->timestamp;
    @endphp

    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Update Password') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ $googleReauthenticated ? __('Google verified your account. Set a new password below.') : __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        @unless ($googleReauthenticated)
            <div>
                <x-input-label for="update_password_current_password" :value="__('Current Password')" />
                <x-password-input id="update_password_current_password" name="current_password" autocomplete="current-password" />
                <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
                <a href="{{ route('google.redirect', ['intent' => 'password']) }}" class="mt-2 inline-block text-sm text-sf-text hover:underline">
                    I use Google — verify my account
                </a>
            </div>
        @endunless

        <div>
            <x-input-label for="update_password_password" :value="__('New Password')" />
            <x-password-input id="update_password_password" name="password" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" />
            <x-password-input id="update_password_password_confirmation" name="password_confirmation" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

        </div>
    </form>
</section>
