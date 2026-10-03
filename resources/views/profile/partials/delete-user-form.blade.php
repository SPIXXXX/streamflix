<section class="space-y-6">
    @php
        $googleReauthenticated = (int) session('google_reauthenticated_user_id') === auth()->id()
            && session('google_reauthenticated_at', 0) >= now()->subMinutes(5)->timestamp;
        $googleDeleteReady = $googleReauthenticated && session('status') === 'google-delete-ready';
    @endphp

    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Delete Account') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('Delete Account') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty() || $googleDeleteReady" :dimmed-backdrop="false" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Are you sure you want to delete your account?') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ $googleReauthenticated ? __('Google verified your account. Confirm below to delete it.') : __('Once your account is deleted, all of its resources and data will be permanently deleted. Enter your password or verify with Google to continue.') }}
            </p>

            @unless ($googleReauthenticated)
                <div class="mt-6">
                    <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />

                    <x-password-input
                        id="password"
                        name="password"
                        class="w-full"
                        placeholder="{{ __('Current password') }}"
                        required
                    />

                    <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
                    <a href="{{ route('google.redirect', ['intent' => 'delete-account']) }}" class="mt-3 inline-block text-sm text-blue-500 hover:underline">
                        I use Google — verify and delete
                    </a>
                </div>
            @endunless

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('Delete Account') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
