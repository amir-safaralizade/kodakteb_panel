document.addEventListener('DOMContentLoaded', () => {
    const digits = value => value.replace(/[۰-۹٠-٩]/g, char => String('۰۱۲۳۴۵۶۷۸۹'.includes(char) ? '۰۱۲۳۴۵۶۷۸۹'.indexOf(char) : '٠١٢٣٤٥٦٧٨٩'.indexOf(char)));
    document.querySelectorAll('[data-clinical-form]').forEach(form => {
        let submitting = false;
        const state = form.querySelector('[data-save-state]');
        const primary = form.querySelector('[data-primary-submit]');
        const submitButtons = [...form.querySelectorAll('button[type="submit"]')];
        const buttonLabels = submitButtons.map(button => button.textContent);
        const snapshot = () => JSON.stringify([...new FormData(form)].filter(([name]) => !['_token', '_method'].includes(name)));
        const money = form.querySelector('[data-money]');
        const moneyOutput = form.querySelector('[data-money-output]');
        const rawMoney = value => digits(value).replace(/[,٬]/g, '').replace(/٫/g, '.').trim();

        function updateMoney(format = false) {
            if (!money) return;
            const raw = rawMoney(money.value);
            const valid = /^(?:\d+(?:\.\d*)?|\.\d+)$/.test(raw) && Number.isFinite(Number(raw));
            money.setCustomValidity(raw && !valid ? 'مبلغ را به صورت عدد مثبت یا صفر وارد کنید.' : '');
            moneyOutput.textContent = valid
                ? new Intl.NumberFormat('fa-IR', { maximumFractionDigits: 3 }).format(Number(raw) / 10) + ' تومان'
                : 'معادل تومان پس از ورود مبلغ نمایش داده می‌شود.';
            if (format && valid) {
                const parts = raw.split('.');
                parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                money.value = parts.join('.');
            }
        }

        function resize(field) {
            field.style.height = 'auto';
            field.style.height = Math.min(field.scrollHeight + 2, 360) + 'px';
            field.style.overflowY = field.scrollHeight > 360 ? 'auto' : 'hidden';
        }
        form.querySelectorAll('[data-autogrow]').forEach(field => {
            resize(field);
            field.addEventListener('input', () => resize(field));
        });
        window.addEventListener('resize', () => form.querySelectorAll('[data-autogrow]').forEach(resize));
        form.querySelectorAll('[data-normalize-digits]').forEach(field => {
            field.addEventListener('blur', () => { field.value = digits(field.value).trim(); updateState(); });
        });
        form.querySelectorAll('[data-jalali-date]').forEach(field => {
            field.addEventListener('blur', () => {
                let value = digits(field.value).trim().replace(/[-.]/g, '/');
                if (/^\d{8}$/.test(value)) value = value.slice(0, 4) + '/' + value.slice(4, 6) + '/' + value.slice(6);
                field.value = value;
                updateState();
            });
        });
        money?.addEventListener('input', () => updateMoney());
        money?.addEventListener('blur', () => { updateMoney(true); updateState(); });
        updateMoney(true);
        const initial = snapshot();
        function dirty() { return snapshot() !== initial; }
        function updateState() {
            state.textContent = dirty() ? 'تغییرات هنوز ذخیره نشده' : 'آماده ورود اطلاعات';
            state.classList.toggle('is-dirty', dirty());
        }
        form.addEventListener('input', () => queueMicrotask(updateState));
        form.addEventListener('change', updateState);
        form.addEventListener('invalid', event => {
            event.target.classList.add('is-invalid');
            event.target.setAttribute('aria-invalid', 'true');
        }, true);
        form.addEventListener('input', event => {
            if (event.target.matches('input, textarea, select') && event.target.validity.valid) {
                event.target.classList.remove('is-invalid');
                event.target.setAttribute('aria-invalid', 'false');
            }
        });
        form.addEventListener('keydown', event => {
            if ((event.ctrlKey || event.metaKey) && event.key === 'Enter' && !event.isComposing) {
                event.preventDefault();
                if (!submitting) form.requestSubmit(primary);
            }
            // Plain Enter must not accidentally submit an unfinished patient form.
            if (event.key === 'Enter' && !event.ctrlKey && !event.metaKey && event.target.matches('input:not([type="hidden"])')) {
                event.preventDefault();
                const controls = [...form.querySelectorAll('input:not([type="hidden"]), select, textarea, button[type="submit"]')].filter(el => !el.disabled && el.getClientRects().length);
                controls[controls.indexOf(event.target) + 1]?.focus();
            }
        });
        form.addEventListener('submit', event => {
            if (submitting) { event.preventDefault(); return; }
            submitting = true;
            form.setAttribute('aria-busy', 'true');
            submitButtons.forEach(button => { button.setAttribute('aria-disabled', 'true'); button.classList.add('is-submitting'); });
            (event.submitter || primary).textContent = 'در حال ذخیره…';
            state.textContent = 'در حال ارسال اطلاعات';
        });
        window.addEventListener('beforeunload', event => {
            if (!submitting && dirty()) { event.preventDefault(); event.returnValue = ''; }
        });
        window.addEventListener('pageshow', () => {
            submitting = false;
            form.removeAttribute('aria-busy');
            submitButtons.forEach((button, index) => { button.removeAttribute('aria-disabled'); button.classList.remove('is-submitting'); button.textContent = buttonLabels[index]; });
            updateState();
        });
        const firstError = form.querySelector('[aria-invalid="true"]');
        if (firstError) firstError.focus();
    });
});
