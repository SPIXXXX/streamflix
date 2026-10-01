<div id="site-confirmation-dialog" tabindex="-1" aria-hidden="true" aria-labelledby="site-confirmation-title" aria-describedby="site-confirmation-description" class="fixed inset-0 z-[70] hidden h-full w-full items-center justify-center overflow-y-auto overflow-x-hidden p-4">
    <div class="relative w-full max-w-md">
        <div class="relative rounded-2xl border border-sf-border bg-sf-surface p-6 shadow-2xl shadow-black/50 sm:p-7">
            <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-full border border-red-500/20 bg-red-500/10 text-red-300">
                <svg class="h-6 w-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.3 3.9 2.8 17a2 2 0 0 0 1.7 3h15a2 2 0 0 0 1.7-3l-7.5-13.1a2 2 0 0 0-3.4 0Z"/></svg>
            </div>
            <h2 id="site-confirmation-title" class="text-lg font-semibold text-white">Please confirm</h2>
            <p id="site-confirmation-description" class="mt-2 text-sm leading-6 text-sf-muted">Are you sure you want to continue?</p>
            <div class="mt-7 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button type="button" data-confirm-cancel class="inline-flex h-10 items-center justify-center rounded-lg border border-sf-border bg-sf-bg px-4 text-sm font-semibold text-gray-200 transition hover:bg-sf-surface-light focus:outline-none focus:ring-2 focus:ring-sf-blue/50">Cancel</button>
                <button type="button" data-confirm-submit class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-red-500/30 bg-red-600 px-4 text-sm font-semibold text-white transition hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-400/60">Confirm</button>
            </div>
        </div>
    </div>
</div>
