<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="avatar" value="Profile picture" />
            <div class="mt-2 flex items-center gap-4">
                <span class="relative inline-flex shrink-0">
                    <x-user-avatar :user="$user" size="h-14 w-14" text-size="text-lg" />
                    <span role="img" aria-label="Online" title="Online" class="absolute bottom-0 right-0 h-4 w-4 rounded-full border-2 border-sf-surface bg-emerald-400"></span>
                </span>
                <div class="flex-1">
                    <p class="mb-1 inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-300"><span class="h-1.5 w-1.5 rounded-full bg-emerald-400" aria-hidden="true"></span>Online now</p>
                    <input id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp" class="block w-full rounded-lg border border-sf-border bg-sf-bg p-2 text-sm text-sf-muted file:me-4 file:rounded-md file:border-0 file:bg-sf-surface-light file:px-3 file:py-2 file:text-sf-text">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">JPEG, PNG, or WebP. Maximum size 2 MB.</p>
                    <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
                </div>
            </div>
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800 dark:text-gray-200">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sf-blue dark:focus:ring-offset-gray-800">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

        </div>
    </form>
</section>
