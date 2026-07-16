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
    const scrollToTop = document.querySelector('[data-scroll-to-top]');
    const topbar = document.querySelector('.topbar');

    const updateTopbar = () => {
        if (topbar instanceof HTMLElement) {
            topbar.classList.toggle('is-scrolled', window.scrollY > 12);
        }
    };

    window.addEventListener('scroll', updateTopbar, { passive: true });
    updateTopbar();

    const updateScrollToTop = () => {
        if (!(scrollToTop instanceof HTMLButtonElement)) {
            return;
        }

        const cookieIsVisible = cookieConsent instanceof HTMLElement && !cookieConsent.hidden;
        scrollToTop.hidden = window.scrollY < 500 || cookieIsVisible;
    };

    if (cookieConsent instanceof HTMLElement && cookieAccept instanceof HTMLButtonElement) {
        const consentKey = 'kotupanovclima_cookie_consent';
        if (localStorage.getItem(consentKey) !== 'accepted') {
            cookieConsent.hidden = false;
        }

        cookieAccept.addEventListener('click', () => {
            localStorage.setItem(consentKey, 'accepted');
            cookieConsent.hidden = true;
            updateScrollToTop();
        });
    }

    if (scrollToTop instanceof HTMLButtonElement) {
        window.addEventListener('scroll', updateScrollToTop, { passive: true });
        scrollToTop.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
            });
        });
        updateScrollToTop();
    }

    document.querySelectorAll('.mobile-menu__link').forEach((link) => {
        link.addEventListener('click', () => {
            const menu = link.closest('details');
            if (menu instanceof HTMLDetailsElement) {
                menu.open = false;
            }
        });
    });

    document.querySelectorAll('.mobile-menu').forEach((menu) => {
        if (!(menu instanceof HTMLDetailsElement)) {
            return;
        }

        menu.addEventListener('toggle', () => {
            const summary = menu.querySelector('summary');
            if (summary instanceof HTMLElement) {
                summary.setAttribute('aria-label', menu.open ? 'Затвори основното меню' : 'Отвори основното меню');
            }
        });
    });

    document.querySelectorAll('[data-captcha-refresh]').forEach((button) => {
        const challenge = button.closest('.captcha-field__challenge');
        const image = challenge ? challenge.querySelector('[data-captcha-image]') : null;
        if (!(button instanceof HTMLButtonElement) || !(image instanceof HTMLImageElement)) {
            return;
        }

        button.addEventListener('click', () => {
            const url = new URL(image.src);
            url.searchParams.set('refresh', String(Date.now()));
            image.src = url.toString();
        });
    });

    const mobileView = window.matchMedia('(max-width: 719px)');

    document.querySelectorAll('[data-catalog-filter-toggle]').forEach((toggle) => {
        const panelId = toggle.getAttribute('aria-controls');
        const panel = panelId ? document.getElementById(panelId) : null;
        if (!(toggle instanceof HTMLButtonElement) || !(panel instanceof HTMLFormElement)) {
            return;
        }

        let expanded = false;
        const updateFilters = () => {
            const isMobile = mobileView.matches;
            toggle.hidden = !isMobile;
            panel.hidden = isMobile && !expanded;
            toggle.setAttribute('aria-expanded', isMobile && expanded ? 'true' : 'false');
        };

        toggle.addEventListener('click', () => {
            expanded = !expanded;
            updateFilters();
        });

        mobileView.addEventListener('change', updateFilters);
        updateFilters();
    });

    document.querySelectorAll('[data-mobile-copy]').forEach((disclosure) => {
        const content = disclosure.querySelector('[data-mobile-copy-content]');
        const toggle = disclosure.querySelector('[data-mobile-copy-toggle]');
        if (!(disclosure instanceof HTMLElement) || !(content instanceof HTMLElement) || !(toggle instanceof HTMLButtonElement)) {
            return;
        }

        const updateMobileCopy = () => {
            if (!mobileView.matches) {
                toggle.hidden = true;
                return;
            }

            if (disclosure.classList.contains('is-expanded')) {
                toggle.hidden = false;
                return;
            }

            requestAnimationFrame(() => {
                toggle.hidden = content.scrollHeight <= content.clientHeight + 1;
            });
        };

        toggle.addEventListener('click', () => {
            const expanded = disclosure.classList.toggle('is-expanded');
            toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            toggle.textContent = expanded ? 'Скрий' : 'Виж още';
            updateMobileCopy();
        });

        mobileView.addEventListener('change', updateMobileCopy);
        window.addEventListener('resize', updateMobileCopy);
        updateMobileCopy();
    });

    document.querySelectorAll('[data-description-disclosure]').forEach((disclosure) => {
        const text = disclosure.querySelector('[data-description-text]');
        const toggle = disclosure.querySelector('[data-description-toggle]');
        if (!(disclosure instanceof HTMLElement) || !(text instanceof HTMLElement) || !(toggle instanceof HTMLButtonElement)) {
            return;
        }

        const updateOverflow = () => {
            if (disclosure.classList.contains('is-expanded')) {
                return;
            }

            toggle.hidden = text.scrollHeight <= text.clientHeight + 1;
        };

        toggle.addEventListener('click', () => {
            const expanded = disclosure.classList.toggle('is-expanded');
            toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            toggle.textContent = expanded ? 'Скрий' : 'Виж още';
        });

        requestAnimationFrame(updateOverflow);
        window.addEventListener('resize', updateOverflow);
    });

    const productImageFile = document.querySelector('[data-product-image-file]');
    const productImagePath = document.querySelector('[data-product-image-path]');
    const productImagePreview = document.querySelector('[data-product-image-preview]');
    const productImagePreviewImg = document.querySelector('[data-product-image-preview-img]');
    const productImagePreviewLabel = document.querySelector('[data-product-image-preview-label]');
    const productImagePreviewHint = document.querySelector('[data-product-image-preview-hint]');
    let productImageObjectUrl = null;

    if (
        productImagePreview instanceof HTMLElement
        && productImagePreviewImg instanceof HTMLImageElement
    ) {
        const setPreview = (src, label, hint) => {
            if (!src) {
                productImagePreview.hidden = true;
                productImagePreviewImg.removeAttribute('src');
                return;
            }

            productImagePreviewImg.src = src;
            if (productImagePreviewLabel) {
                productImagePreviewLabel.textContent = label;
            }
            if (productImagePreviewHint) {
                productImagePreviewHint.textContent = hint;
            }
            productImagePreview.hidden = false;
        };

        const clearObjectUrl = () => {
            if (productImageObjectUrl) {
                URL.revokeObjectURL(productImageObjectUrl);
                productImageObjectUrl = null;
            }
        };

        if (productImageFile instanceof HTMLInputElement) {
            productImageFile.addEventListener('change', () => {
                clearObjectUrl();

                const file = productImageFile.files ? productImageFile.files[0] : null;
                if (!file) {
                    const fallbackPath = productImagePath instanceof HTMLInputElement ? productImagePath.value.trim() : '';
                    setPreview(fallbackPath, 'Преглед от въведения път', 'Снимката ще се използва от този път, ако не качиш нов файл.');
                    return;
                }

                productImageObjectUrl = URL.createObjectURL(file);
                setPreview(productImageObjectUrl, 'Нова избрана снимка', 'Това е локален преглед. Файлът ще бъде качен след натискане на "Запиши продукта".');
            });
        }

        if (productImagePath instanceof HTMLInputElement) {
            productImagePath.addEventListener('input', () => {
                if (productImageFile instanceof HTMLInputElement && productImageFile.files && productImageFile.files.length > 0) {
                    return;
                }

                const path = productImagePath.value.trim();
                setPreview(path, 'Преглед от въведения път', 'Снимката ще се използва от този път, ако не качиш нов файл.');
            });
        }
    }

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
