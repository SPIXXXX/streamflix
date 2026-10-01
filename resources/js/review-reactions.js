document.addEventListener('click', async (event) => {
    const button = event.target.closest('button.reaction[data-reaction-url]');
    if (!button || button.disabled) {
        return;
    }

    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!token) {
        window.dispatchEvent(new CustomEvent('app:toast', {
            detail: { type: 'error', message: 'Could not verify your session. Refresh the page and try again.' },
        }));
        return;
    }

    button.disabled = true;
    button.setAttribute('aria-busy', 'true');

    try {
        const response = await fetch(button.dataset.reactionUrl, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ reaction: button.dataset.reaction }),
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            throw new Error(data.message || 'Could not save your reaction. Please try again.');
        }

        const reviewId = button.dataset.reviewId;
        const agreeCount = document.querySelector(`.reaction-agree-${reviewId}`);
        const disagreeCount = document.querySelector(`.reaction-disagree-${reviewId}`);
        if (agreeCount) {
            agreeCount.textContent = data.agree;
        }
        if (disagreeCount) {
            disagreeCount.textContent = data.disagree;
        }
    } catch (error) {
        window.dispatchEvent(new CustomEvent('app:toast', {
            detail: { type: 'error', message: error.message || 'Could not save your reaction. Please try again.' },
        }));
    } finally {
        button.disabled = false;
        button.removeAttribute('aria-busy');
    }
});
