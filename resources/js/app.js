import '@tabler/core/dist/js/tabler.min.js';

function normalizePhone(phone) {
    const digits = String(phone).replace(/\D/g, '');

    if (digits.startsWith('90')) {
        return digits;
    }

    if (digits.startsWith('0')) {
        return `9${digits}`;
    }

    if (digits.length === 10) {
        return `90${digits}`;
    }

    return digits;
}

function updateWhatsAppTools() {
    const messageInput = document.querySelector('#whatsappMessage');
    const phonesInput = document.querySelector('#whatsappPhones');
    const openLink = document.querySelector('#whatsappOpenLink');
    const recipients = [...document.querySelectorAll('[data-whatsapp-recipient]:checked')];

    if (!messageInput || !phonesInput || !openLink) {
        return;
    }

    const message = messageInput.value.trim();
    const phones = recipients
        .map((input) => normalizePhone(input.value))
        .filter(Boolean);
    const uniquePhones = [...new Set(phones)];
    const encodedMessage = encodeURIComponent(message);
    const link = uniquePhones.length === 1
        ? `https://wa.me/${uniquePhones[0]}?text=${encodedMessage}`
        : `https://wa.me/?text=${encodedMessage}`;

    phonesInput.value = uniquePhones.join('\n');
    openLink.href = link;
}

document.addEventListener('DOMContentLoaded', () => {
    const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
    const sidebarBackdrop = document.querySelector('[data-sidebar-backdrop]');

    function closeSidebar() {
        document.body.classList.remove('sidebar-open');
        sidebarToggle?.setAttribute('aria-expanded', 'false');
    }

    function toggleSidebar() {
        const isOpen = document.body.classList.toggle('sidebar-open');
        sidebarToggle?.setAttribute('aria-expanded', String(isOpen));
    }

    sidebarToggle?.addEventListener('click', toggleSidebar);
    sidebarBackdrop?.addEventListener('click', closeSidebar);
    document.querySelectorAll('.app-sidebar .sidebar-link').forEach((link) => {
        link.addEventListener('click', closeSidebar);
    });

    const confirmModal = document.querySelector('[data-confirm-modal]');
    const confirmMessage = confirmModal?.querySelector('[data-confirm-message]');
    const confirmAccept = confirmModal?.querySelector('[data-confirm-accept]');
    const confirmCancel = confirmModal?.querySelector('[data-confirm-cancel]');
    let pendingConfirmForm = null;

    function closeConfirmModal() {
        confirmModal?.classList.remove('show');
        confirmModal?.setAttribute('aria-hidden', 'true');
        pendingConfirmForm = null;
    }

    function openConfirmModal(form) {
        pendingConfirmForm = form;

        if (confirmMessage) {
            confirmMessage.textContent = form.dataset.confirm || 'Bu işlem devam etsin mi?';
        }

        confirmModal?.classList.add('show');
        confirmModal?.setAttribute('aria-hidden', 'false');
        confirmCancel?.focus();
    }

    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (form.dataset.confirmed === 'true') {
                return;
            }

            event.preventDefault();
            openConfirmModal(form);
        });
    });

    confirmAccept?.addEventListener('click', () => {
        if (!pendingConfirmForm) {
            return;
        }

        pendingConfirmForm.dataset.confirmed = 'true';
        pendingConfirmForm.requestSubmit();
        closeConfirmModal();
    });

    confirmCancel?.addEventListener('click', closeConfirmModal);

    confirmModal?.addEventListener('click', (event) => {
        if (event.target === confirmModal) {
            closeConfirmModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeSidebar();
        }

        if (event.key === 'Escape' && confirmModal?.classList.contains('show')) {
            closeConfirmModal();
        }
    });

    document.querySelectorAll('[data-copy-target]').forEach((button) => {
        button.addEventListener('click', async () => {
            const target = document.querySelector(button.dataset.copyTarget);

            if (!target) {
                return;
            }

            await navigator.clipboard.writeText(target.value || target.textContent || '');
            button.classList.add('btn-success');
            setTimeout(() => button.classList.remove('btn-success'), 900);
        });
    });

    document.querySelectorAll('[data-toggle-checks]').forEach((button) => {
        button.addEventListener('click', () => {
            const inputs = [...document.querySelectorAll(button.dataset.toggleChecks)];
            const shouldCheck = inputs.some((input) => !input.checked);

            inputs.forEach((input) => {
                input.checked = shouldCheck;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });
    });

    document.querySelectorAll('[data-manual-match-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const row = document.getElementById(button.dataset.manualMatchToggle);

            if (!row) {
                return;
            }

            row.classList.toggle('d-none');
            button.classList.toggle('btn-outline-primary');
            button.classList.toggle('btn-primary');
        });
    });

    document.querySelectorAll('[data-add-block]').forEach((button) => {
        button.addEventListener('click', () => {
            const list = button.closest('form')?.querySelector('[data-block-list]');

            if (!list) {
                return;
            }

            const row = list.querySelector('.input-group')?.cloneNode(true);

            if (!row) {
                return;
            }

            row.querySelector('input').value = '';
            list.appendChild(row);
            row.querySelector('input')?.focus();
        });
    });

    document.querySelectorAll('[data-announcement-ticker]').forEach((ticker) => {
        const count = Number(ticker.dataset.announcementCount || 0);
        const track = ticker.querySelector('.topbar-announcement-track');
        const item = ticker.querySelector('.topbar-announcement-item');

        if (!track || !item || count <= 1) {
            return;
        }

        const itemHeight = item.getBoundingClientRect().height || 40;
        let index = 0;

        window.setInterval(() => {
            index += 1;
            track.style.transition = 'transform .45s cubic-bezier(.45, 0, .2, 1)';
            track.style.transform = `translateY(-${index * itemHeight}px)`;

            if (index === count) {
                window.setTimeout(() => {
                    track.style.transition = 'none';
                    track.style.transform = 'translateY(0)';
                    index = 0;
                }, 480);
            }
        }, 4000);
    });

    document.querySelector('#whatsappMessage')?.addEventListener('input', updateWhatsAppTools);
    document.querySelectorAll('[data-whatsapp-recipient]').forEach((input) => {
        input.addEventListener('change', updateWhatsAppTools);
    });

    updateWhatsAppTools();
});
