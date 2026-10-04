<div x-data="{ visible: false }" class="relative mt-1 block w-full">
    <input
        {{ $attributes->class([
            'block w-full pr-12',
            'border-gray-300 dark:border-gray-700 dark:bg-sf-bg dark:text-gray-300',
            'focus:border-sf-blue dark:focus:border-sf-blue focus:ring-sf-blue dark:focus:ring-sf-blue',
            'rounded-md shadow-sm',
        ])->merge(['type' => 'password']) }}
        x-bind:type="visible ? 'text' : 'password'"
    >
    <button
        type="button"
        x-on:click="visible = ! visible"
        aria-label="Show password"
        x-bind:aria-label="visible ? 'Hide password' : 'Show password'"
        x-bind:aria-pressed="visible"
        class="absolute inset-y-0 right-0 z-10 inline-flex w-11 items-center justify-center rounded-r-lg text-sf-muted transition hover:text-sf-text focus:outline-none focus:ring-2 focus:ring-inset focus:ring-sf-blue"
    >
        <svg x-show="! visible" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-6.75 9.75-6.75S21.75 12 21.75 12s-3.5 6.75-9.75 6.75S2.25 12 2.25 12Z" />
            <circle cx="12" cy="12" r="3" />
        </svg>
        <svg style="display: none;" x-show="visible" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.58 10.59a2 2 0 0 0 2.83 2.83M9.88 5.24A10.9 10.9 0 0 1 12 5.04c6.25 0 9.75 6.96 9.75 6.96a16.6 16.6 0 0 1-3.05 3.8M6.18 6.18C3.55 8.08 2.25 12 2.25 12s3.5 6.96 9.75 6.96c1.1 0 2.1-.2 3-.55" />
        </svg>
    </button>
</div>
