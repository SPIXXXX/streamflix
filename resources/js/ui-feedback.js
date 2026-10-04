import { Modal } from 'flowbite';

const toastStyles = {
    success: { container: 'border-emerald-500/25 bg-emerald-950/95 text-emerald-100', icon: 'bg-emerald-500/15 text-emerald-300', symbol: '✓', label: 'Success', role: 'status' },
    error: { container: 'border-red-500/25 bg-red-950/95 text-red-100', icon: 'bg-red-500/15 text-red-300', symbol: '!', label: 'Error', role: 'alert' },
    warning: { container: 'border-amber-500/25 bg-amber-950/95 text-amber-100', icon: 'bg-amber-500/15 text-amber-300', symbol: '⚠', label: 'Warning', role: 'status' },
    info: { container: 'border-sky-500/25 bg-sky-950/95 text-sky-100', icon: 'bg-sky-500/15 text-sky-300', symbol: 'i', label: 'Information', role: 'status' },
};

function dismissToast(toast) {
    if (!toast || toast.dataset.dismissing === 'true') {
        return;
    }

    toast.dataset.dismissing = 'true';
    toast.classList.add('translate-y-2', 'opacity-0');
    window.setTimeout(() => toast.remove(), 220);
}

function createToast(type, message) {
    const style = toastStyles[type] || toastStyles.info;
    let container = document.querySelector('[data-toast-container]');

    if (!container) {
        container = document.createElement('div');
        container.dataset.toastContainer = '';
        container.setAttribute('aria-live', 'polite');
        container.setAttribute('aria-atomic', 'false');
        container.className = 'pointer-events-none fixed inset-x-0 top-20 z-[50] flex flex-col items-center gap-3 px-4 sm:inset-x-auto sm:right-6 sm:top-24 sm:w-full sm:max-w-sm sm:items-end sm:px-0';
        document.body.append(container);
    }

    const toast = document.createElement('div');
    toast.dataset.toast = '';
    toast.setAttribute('role', style.role);
    toast.className = `pointer-events-auto flex w-full items-start gap-3 rounded-xl border px-4 py-3.5 text-sm shadow-2xl shadow-black/30 backdrop-blur transition duration-200 ${style.container} translate-y-2 opacity-0`;

    const icon = document.createElement('span');
    icon.className = `mt-0.5 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-bold ${style.icon}`;
    icon.setAttribute('aria-hidden', 'true');
    icon.textContent = style.symbol;

    const text = document.createElement('p');
    text.className = 'min-w-0 flex-1 leading-6';
    const screenReaderLabel = document.createElement('span');
    screenReaderLabel.className = 'sr-only';
    screenReaderLabel.textContent = `${style.label}: `;
    text.append(screenReaderLabel, document.createTextNode(message));

    const close = document.createElement('button');
    close.type = 'button';
    close.dataset.toastClose = '';
    close.setAttribute('aria-label', 'Dismiss notification');
    close.className = '-me-1 -mt-1 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-current/70 transition hover:bg-white/10 hover:text-current focus:outline-none focus:ring-2 focus:ring-white/40';
    close.innerHTML = '<svg class="h-4 w-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 12 12M13 1 1 13"/></svg>';

    toast.append(icon, text, close);
    container.append(toast);
    requestAnimationFrame(() => toast.classList.remove('translate-y-2', 'opacity-0'));
    window.setTimeout(() => dismissToast(toast), type === 'error' ? 8000 : 5500);
}

function initializeFeedback() {
    document.addEventListener('click', () => {
        document.querySelectorAll('button[data-dropdown-toggle^="account-actions-"]').forEach((trigger) => {
            const menu = document.getElementById(trigger.dataset.dropdownToggle);
            trigger.setAttribute('aria-expanded', String(Boolean(menu && !menu.classList.contains('hidden'))));
        });
    });

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || event.defaultPrevented) {
            return;
        }

        const isAdminPost = Boolean(form.closest('[data-admin-area]')) && ['post', 'put', 'patch', 'delete'].includes(form.method.toLowerCase());
        if (!form.hasAttribute('data-loading-form') && !isAdminPost) {
            return;
        }

        const button = event.submitter instanceof HTMLButtonElement
            ? event.submitter
            : form.querySelector('button[type="submit"], button:not([type]), input[type="submit"]');
        if (!button || button.dataset.loading === 'true') {
            return;
        }

        button.dataset.loading = 'true';
        button.setAttribute('aria-busy', 'true');
        button.disabled = true;
        button.classList.add('cursor-wait');
    });

    document.querySelectorAll('[data-toast]').forEach((toast) => {
        requestAnimationFrame(() => toast.classList.remove('translate-y-2', 'opacity-0'));
        window.setTimeout(() => dismissToast(toast), toast.getAttribute('role') === 'alert' ? 8000 : 5500);
    });

    document.addEventListener('click', (event) => {
        const close = event.target.closest('[data-toast-close]');
        if (close) {
            dismissToast(close.closest('[data-toast]'));
        }
    });

    window.addEventListener('app:toast', (event) => {
        const { type = 'info', message } = event.detail || {};
        if (typeof message === 'string' && message.trim() !== '') {
            createToast(type, message);
        }
    });

    const dialogElement = document.getElementById('site-confirmation-dialog');
    if (!dialogElement) {
        return;
    }

    const titleElement = document.getElementById('site-confirmation-title');
    const descriptionElement = document.getElementById('site-confirmation-description');
    const confirmButton = dialogElement.querySelector('[data-confirm-submit]');
    const cancelButton = dialogElement.querySelector('[data-confirm-cancel]');
    const approvedForms = new WeakSet();
    let pendingForm = null;
    let pendingSubmitter = null;
    let returnFocusTo = null;
    let confirmed = false;

    const modal = new Modal(dialogElement, {
        placement: 'center',
        backdrop: 'dynamic',
        backdropClasses: 'bg-black/70 fixed inset-0 z-[60]',
        closable: true,
        onShow: () => cancelButton.focus(),
        onHide: () => {
            const form = pendingForm;
            const submitter = pendingSubmitter;
            const shouldSubmit = confirmed;
            pendingForm = null;
            pendingSubmitter = null;
            confirmed = false;

            if (shouldSubmit && form?.isConnected) {
                approvedForms.add(form);
                if (submitter && submitter.isConnected) {
                    form.requestSubmit(submitter);
                } else {
                    form.requestSubmit();
                }
                window.setTimeout(() => approvedForms.delete(form), 0);
                return;
            }

            if (returnFocusTo?.isConnected) {
                returnFocusTo.focus();
            }
        },
    });

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-confirm')) {
            return;
        }

        if (approvedForms.has(form)) {
            approvedForms.delete(form);
            return;
        }

        event.preventDefault();
        const accountMenu = form.closest('[id^="account-actions-"]');
        if (accountMenu) {
            window.FlowbiteInstances?.getInstance('Dropdown', accountMenu.id)?.hide();
            document.getElementById(`${accountMenu.id}-button`)?.setAttribute('aria-expanded', 'false');
        }
        pendingForm = form;
        pendingSubmitter = event.submitter;
        returnFocusTo = event.submitter || document.activeElement;
        titleElement.textContent = form.dataset.confirmTitle || 'Please confirm';
        descriptionElement.textContent = form.dataset.confirmMessage || 'Are you sure you want to continue?';
        confirmButton.textContent = form.dataset.confirmLabel || 'Confirm';
        const isPrimaryAction = form.dataset.confirmStyle === 'primary';
        confirmButton.classList.toggle('border-red-500/30', !isPrimaryAction);
        confirmButton.classList.toggle('border-sf-blue', isPrimaryAction);
        confirmButton.classList.toggle('bg-red-600', !isPrimaryAction);
        confirmButton.classList.toggle('hover:bg-red-500', !isPrimaryAction);
        confirmButton.classList.toggle('focus:ring-red-400/60', !isPrimaryAction);
        confirmButton.classList.toggle('bg-sf-blue', isPrimaryAction);
        confirmButton.classList.toggle('hover:bg-sf-blue-dark', isPrimaryAction);
        confirmButton.classList.toggle('focus:ring-sf-blue/50', isPrimaryAction);
        modal.show();
    }, true);

    confirmButton.addEventListener('click', () => {
        confirmed = true;
        modal.hide();
    });
    cancelButton.addEventListener('click', () => modal.hide());

    dialogElement.addEventListener('keydown', (event) => {
        if (event.key !== 'Tab') {
            return;
        }

        const focusable = [...dialogElement.querySelectorAll('button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])')];
        if (!focusable.length) {
            event.preventDefault();
            return;
        }

        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeFeedback, { once: true });
} else {
    initializeFeedback();
}
