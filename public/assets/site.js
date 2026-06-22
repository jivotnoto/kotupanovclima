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
})();
