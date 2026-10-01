document.addEventListener('submit', async (event) => {
    const form = event.target.closest('[data-favorite-form]');

    if (!form) {
        return;
    }

    event.preventDefault();

    const button = form.querySelector('[data-favorite-button]');
    const label = form.querySelector('[data-favorite-label]');
    const icon = form.querySelector('[data-favorite-icon]');

    button.disabled = true;
    button.setAttribute('aria-busy', 'true');

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
            },
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Could not update favorites.');
        }

        button.setAttribute('aria-pressed', String(data.is_favorite));
        label.textContent = data.is_favorite ? 'Favorited' : 'Favorite';
        icon.setAttribute('fill', data.is_favorite ? 'currentColor' : 'none');
        button.classList.toggle('border-pink-500/40', data.is_favorite);
        button.classList.toggle('bg-pink-500/10', data.is_favorite);
        button.classList.toggle('text-pink-300', data.is_favorite);
        button.classList.toggle('hover:bg-pink-500/20', data.is_favorite);
        button.classList.toggle('border-sf-border', !data.is_favorite);
        button.classList.toggle('bg-sf-surface', !data.is_favorite);
        button.classList.toggle('text-gray-200', !data.is_favorite);
        button.classList.toggle('hover:border-pink-500/50', !data.is_favorite);
        button.classList.toggle('hover:text-pink-300', !data.is_favorite);
        window.dispatchEvent(new CustomEvent('app:toast', { detail: { type: 'success', message: data.message } }));
    } catch (error) {
        window.dispatchEvent(new CustomEvent('app:toast', { detail: { type: 'error', message: error.message } }));
    } finally {
        button.disabled = false;
        button.removeAttribute('aria-busy');
    }
});
