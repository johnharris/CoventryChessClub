/**
 * Mobile navigation and any small dropdowns.
 *
 * The markup is complete and usable without JavaScript; this only adds the
 * open/close behaviour and the accessibility state.
 */

export const mountNavigation = () => {
    const toggle = document.querySelector('[data-nav-toggle]');
    const panel = document.querySelector('[data-nav-panel]');

    if (toggle && panel) {
        const setOpen = (open) => {
            panel.hidden = !open;
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            document.body.classList.toggle('overflow-hidden', open);
        };

        toggle.addEventListener('click', () => setOpen(panel.hidden));

        panel.querySelectorAll('a').forEach((link) =>
            link.addEventListener('click', () => setOpen(false))
        );

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !panel.hidden) setOpen(false);
        });

        // Reset when resizing up to desktop, so the panel cannot be stuck open.
        window.matchMedia('(min-width: 768px)').addEventListener('change', (event) => {
            if (event.matches) setOpen(false);
        });

        setOpen(false);
    }

    // Small "user menu" style dropdowns.
    document.querySelectorAll('[data-dropdown]').forEach((dropdown) => {
        const button = dropdown.querySelector('[data-dropdown-toggle]');
        const menu = dropdown.querySelector('[data-dropdown-menu]');

        if (!button || !menu) return;

        const close = () => {
            menu.hidden = true;
            button.setAttribute('aria-expanded', 'false');
        };

        button.addEventListener('click', (event) => {
            event.stopPropagation();

            const open = menu.hidden;

            menu.hidden = !open;
            button.setAttribute('aria-expanded', open ? 'true' : 'false');
        });

        document.addEventListener('click', (event) => {
            if (!dropdown.contains(event.target)) close();
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') close();
        });

        close();
    });

    // Confirm destructive actions without a bespoke modal.
    document.querySelectorAll('[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm)) event.preventDefault();
        });
    });

    // Copy-to-clipboard for invitation links and FENs.
    document.querySelectorAll('[data-copy]').forEach((button) => {
        button.addEventListener('click', async () => {
            const target = document.querySelector(button.dataset.copy);
            const text = target ? (target.value ?? target.textContent) : button.dataset.copyText;

            if (!text) return;

            try {
                await navigator.clipboard.writeText(text.trim());

                const original = button.dataset.copyLabel ?? button.textContent;

                button.dataset.copyLabel = original;
                button.textContent = 'Copied';

                window.setTimeout(() => { button.textContent = original; }, 1600);
            } catch {
                target?.select?.();
            }
        });
    });
};
