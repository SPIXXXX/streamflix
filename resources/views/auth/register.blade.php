<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" data-loading-form class="space-y-4">
        @csrf

        <div>
            <x-input-label for="name" value="Name" class="!text-sf-muted" />
            <x-text-input id="name" class="block mt-1 w-full bg-sf-bg border-sf-border text-sf-text rounded-lg focus:border-sf-blue focus:ring-sf-blue" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" value="Email" class="!text-sf-muted" />
            <x-text-input id="email" class="block mt-1 w-full bg-sf-bg border-sf-border text-sf-text rounded-lg focus:border-sf-blue focus:ring-sf-blue" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="avatar" value="Profile picture (optional)" class="!text-sf-muted" />
            <input id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full rounded-lg border border-sf-border bg-sf-bg p-2 text-sm text-sf-muted file:me-4 file:rounded-md file:border-0 file:bg-sf-surface-light file:px-3 file:py-2 file:text-sf-text">
            <p class="mt-1 text-xs text-sf-muted">JPEG, PNG, or WebP. Maximum size 2 MB.</p>
            <x-input-error :messages="$errors->get('avatar')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" value="Password" class="!text-sf-muted" />
            <x-password-input id="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Confirm Password" class="!text-sf-muted" />
            <x-password-input id="password_confirmation" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <button type="submit" class="w-full bg-sf-blue hover:bg-sf-blue-dark text-white font-medium py-2.5 rounded-lg shadow-glow-blue transition">
            Register
        </button>

        <p class="text-center text-sm text-sf-muted">
            Already registered?
            <a href="{{ route('login') }}" class="text-sf-text hover:underline">Log in</a>
        </p>
    </form>
</x-guest-layout>
