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

    const reviewId = button.dataset.reviewId;
    const reviewCard = document.getElementById(`review-${reviewId}`);
    const agreeCount = reviewCard?.querySelector(`.reaction-agree-${reviewId}`);
    const disagreeCount = reviewCard?.querySelector(`.reaction-disagree-${reviewId}`);
    const otherButton = reviewCard?.querySelector(`button.reaction[data-reaction]:not([data-reaction="${button.dataset.reaction}"])`);
    const previous = {
        agree: Number(agreeCount?.textContent ?? 0),
        disagree: Number(disagreeCount?.textContent ?? 0),
        active: button.dataset.active === 'true',
        otherActive: otherButton?.dataset.active === 'true',
    };
    const selected = !previous.active;
    const activeColor = (reaction) => reaction === 'agree' ? 'text-emerald-400' : 'text-rose-400';
    const activeBackground = (reaction) => reaction === 'agree' ? 'bg-emerald-500/10' : 'bg-rose-500/10';
    const setActive = (target, active) => {
        if (!target) {
            return;
        }

        target.dataset.active = String(active);
        target.classList.toggle(activeColor(target.dataset.reaction), active);
        target.classList.toggle(activeBackground(target.dataset.reaction), active);
    };
    const updateCount = (target, value) => {
        if (target) {
            target.textContent = String(Math.max(0, value));
        }
    };

    if (selected) {
        updateCount(button.dataset.reaction === 'agree' ? agreeCount : disagreeCount, (button.dataset.reaction === 'agree' ? previous.agree : previous.disagree) + 1);
        if (previous.otherActive) {
            updateCount(button.dataset.reaction === 'agree' ? disagreeCount : agreeCount, (button.dataset.reaction === 'agree' ? previous.disagree : previous.agree) - 1);
        }
    } else {
        updateCount(button.dataset.reaction === 'agree' ? agreeCount : disagreeCount, (button.dataset.reaction === 'agree' ? previous.agree : previous.disagree) - 1);
    }
    setActive(button, selected);
    setActive(otherButton, selected ? false : previous.otherActive);

    button.disabled = true;
    button.setAttribute('aria-busy', 'true');
    button.dataset.animating = 'true';
    window.setTimeout(() => delete button.dataset.animating, 550);

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

        updateCount(agreeCount, data.agree);
        updateCount(disagreeCount, data.disagree);
        setActive(button, data.status !== 'removed');
        if (data.status !== 'removed') {
            setActive(otherButton, false);
        }
    } catch (error) {
        updateCount(agreeCount, previous.agree);
        updateCount(disagreeCount, previous.disagree);
        setActive(button, previous.active);
        setActive(otherButton, previous.otherActive);
        window.dispatchEvent(new CustomEvent('app:toast', {
            detail: { type: 'error', message: error.message || 'Could not save your reaction. Please try again.' },
        }));
    } finally {
        button.disabled = false;
        button.removeAttribute('aria-busy');
    }
});
