const heartbeatUrl = document.querySelector('meta[name="heartbeat-url"]')?.content;
const statusUrl = document.querySelector('meta[name="user-activity-status-url"]')?.content;
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

if (heartbeatUrl && csrfToken) {
    const sendHeartbeat = () => {
        fetch(heartbeatUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
        }).catch(() => {});
    };

    let heartbeatInterval;
    const startHeartbeat = () => {
        if (heartbeatInterval) {
            return;
        }

        sendHeartbeat();
        heartbeatInterval = window.setInterval(sendHeartbeat, 60_000);
    };
    const stopHeartbeat = () => {
        window.clearInterval(heartbeatInterval);
        heartbeatInterval = undefined;
    };

    startHeartbeat();
    window.addEventListener('pagehide', stopHeartbeat);
    window.addEventListener('pageshow', startHeartbeat);
}

if (statusUrl) {
    const refreshVisibleStatuses = async () => {
        const statuses = [...document.querySelectorAll('[data-user-activity-status]')];
        const userIds = [...new Set(statuses.map((status) => status.dataset.userId))];
        if (document.hidden || userIds.length === 0) {
            return;
        }

        const query = new URLSearchParams();
        userIds.forEach((userId) => query.append('users[]', userId));

        try {
            const response = await fetch(`${statusUrl}?${query}`, {
                credentials: 'same-origin',
                headers: { Accept: 'application/json' },
            });
            if (!response.ok) {
                return;
            }

            const freshStatuses = await response.json();
            const byUserId = new Map(freshStatuses.map((status) => [String(status.id), status]));
            statuses.forEach((element) => {
                const status = byUserId.get(element.dataset.userId);
                if (!status) {
                    return;
                }

                document.querySelectorAll(`[data-user-online-indicator][data-user-id="${status.id}"]`).forEach((dot) => {
                    dot.classList.toggle('hidden', !status.online);
                });

                const indicator = element.querySelector('.activity-status-indicator');
                element.querySelector('[data-activity-status-label]').textContent = status.label;
                element.setAttribute('aria-label', status.label);
                element.classList.toggle('font-semibold', status.online);
                element.classList.toggle('text-emerald-300', status.online);
                element.classList.toggle('text-sf-muted', !status.online);
                indicator.classList.toggle('bg-emerald-400', status.online);
                indicator.classList.toggle('bg-slate-500', !status.online);
            });
        } catch {
            // Leave the last server-rendered status visible when a refresh fails.
        }
    };

    let statusInterval;
    const startStatusRefresh = () => {
        if (statusInterval) {
            return;
        }

        refreshVisibleStatuses();
        statusInterval = window.setInterval(refreshVisibleStatuses, 60_000);
    };
    const stopStatusRefresh = () => {
        window.clearInterval(statusInterval);
        statusInterval = undefined;
    };

    startStatusRefresh();
    window.addEventListener('pagehide', stopStatusRefresh);
    window.addEventListener('pageshow', startStatusRefresh);
}
