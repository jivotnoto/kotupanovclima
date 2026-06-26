(() => {
    const rate = 1.95583;

    const formatBgn = (value) => {
        if (!Number.isFinite(value)) {
            return 'Ще се изчисли след въвеждане';
        }

        return `${value.toFixed(2).replace('.', ',')} лв.`;
    };

    document.querySelectorAll('[data-eur-input]').forEach((input) => {
        const outputId = input.getAttribute('data-eur-input');
        const output = outputId ? document.getElementById(outputId) : null;
        if (!output) {
            return;
        }

        const update = () => {
            const normalized = String(input.value || '').replace(',', '.').trim();
            const value = Number.parseFloat(normalized);
            output.textContent = formatBgn(Number.isFinite(value) ? value * rate : Number.NaN);
        };

        input.addEventListener('input', update);
        input.addEventListener('change', update);
        update();
    });

    const cookieConsent = document.querySelector('[data-cookie-consent]');
    const cookieAccept = document.querySelector('[data-cookie-consent-accept]');
    if (cookieConsent instanceof HTMLElement && cookieAccept instanceof HTMLButtonElement) {
        const consentKey = 'kotupanovclima_cookie_consent';
        if (localStorage.getItem(consentKey) !== 'accepted') {
            cookieConsent.hidden = false;
        }

        cookieAccept.addEventListener('click', () => {
            localStorage.setItem(consentKey, 'accepted');
            cookieConsent.hidden = true;
        });
    }

    document.querySelectorAll('.mobile-menu__link').forEach((link) => {
        link.addEventListener('click', () => {
            const menu = link.closest('details');
            if (menu instanceof HTMLDetailsElement) {
                menu.open = false;
            }
        });
    });

    const imageViewer = document.querySelector('[data-image-viewer]');
    if (!imageViewer) {
        return;
    }

    const image = imageViewer.querySelector('[data-image-viewer-image]');
    const openButtons = document.querySelectorAll('[data-image-viewer-open]');
    const closeButtons = imageViewer.querySelectorAll('[data-image-viewer-close]');

    const closeViewer = () => {
        imageViewer.hidden = true;
        document.body.classList.remove('has-image-viewer');
    };

    const openViewer = (button) => {
        if (!(button instanceof HTMLElement) || !(image instanceof HTMLImageElement)) {
            return;
        }

        const src = button.getAttribute('data-image-src');
        const alt = button.getAttribute('data-image-alt') || '';
        if (src) {
            image.src = src;
            image.alt = alt;
        }

        imageViewer.hidden = false;
        document.body.classList.add('has-image-viewer');
    };

    openButtons.forEach((button) => {
        button.addEventListener('click', () => openViewer(button));
    });

    closeButtons.forEach((button) => {
        button.addEventListener('click', closeViewer);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !imageViewer.hidden) {
            closeViewer();
        }
    });
})();
