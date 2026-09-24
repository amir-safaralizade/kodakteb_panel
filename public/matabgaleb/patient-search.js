document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('[data-suggestions-url]');
    if (!form) return;
    const input = form.querySelector('input[type="search"]');
    const panel = form.querySelector('.search-suggestions');
    const list = form.querySelector('.suggestions-list');
    const message = form.querySelector('.suggestions-message');
    const status = form.querySelector('[role="status"]');
    let timer, controller, version = 0;
    let composing = false;

    function close() {
        clearTimeout(timer);
        controller?.abort();
        version++;
        panel.hidden = true;
        input.setAttribute('aria-expanded', 'false');
        input.removeAttribute('aria-busy');
        status.textContent = '';
    }

    function showMessage(text) {
        list.replaceChildren();
        message.textContent = text;
        message.hidden = false;
        panel.hidden = false;
        input.setAttribute('aria-expanded', 'true');
        status.textContent = text;
    }

    function addText(parent, tag, className, text) {
        const element = document.createElement(tag);
        element.className = className;
        element.textContent = text;
        parent.append(element);
        return element;
    }

    function render(items) {
        list.replaceChildren();
        if (!items.length) {
            showMessage('پرونده‌ای پیدا نشد؛ نام یا شماره را دقیق‌تر بررسی کنید.');
            return;
        }
        message.hidden = true;
        items.slice(0, 5).forEach(item => {
            const row = document.createElement('li');
            const profile = document.createElement('a');
            profile.className = 'suggestion-profile';
            profile.href = item.profile_url;
            addText(profile, 'strong', 'suggestion-name', item.name);
            const meta = addText(profile, 'span', 'suggestion-meta', 'پرونده ' + (item.case_number ?? '—'));
            if (item.national_code) addText(meta, 'span', '', 'کد ملی ' + item.national_code);
            const visit = document.createElement('a');
            visit.className = 'suggestion-visit';
            visit.href = item.visit_url;
            visit.textContent = 'ثبت ویزیت';
            visit.setAttribute('aria-label', 'ثبت ویزیت برای ' + item.name);
            row.append(profile, visit);
            list.append(row);
        });
        panel.hidden = false;
        input.setAttribute('aria-expanded', 'true');
        status.textContent = Math.min(items.length, 5) + ' پرونده مرتبط پیدا شد. با کلید پایین یا تب وارد پیشنهادها شوید.';
    }

    function schedule() {
        close();
        const query = input.value.trim();
        const exactCaseNumber = /^[0-9۰-۹٠-٩]$/.test(query);
        if (composing || (query.length < 2 && !exactCaseNumber) || !/[\p{L}\p{N}]/u.test(query)) return;
        const requestVersion = version;
        timer = setTimeout(async () => {
            controller = new AbortController();
            input.setAttribute('aria-busy', 'true');
            showMessage('در حال جست‌وجوی پرونده…');
            try {
                const url = new URL(form.dataset.suggestionsUrl, location.href);
                url.searchParams.set('q', query);
                const response = await fetch(url, {
                    signal: controller.signal,
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin',
                    cache: 'no-store',
                });
                if (requestVersion !== version) return;
                if (response.status === 401) throw new Error('session');
                if (response.status === 429) throw new Error('rate');
                if (!response.ok) throw new Error('network');
                const data = await response.json();
                if (requestVersion !== version) return;
                render(data.items);
            } catch (error) {
                if (error.name === 'AbortError' || requestVersion !== version) return;
                showMessage(error.message === 'session'
                    ? 'نشست شما تمام شده؛ دوباره وارد حساب شوید.'
                    : error.message === 'rate'
                        ? 'چند لحظه صبر کنید و دوباره جست‌وجو کنید.'
                        : 'پیشنهادها در دسترس نیستند؛ از دکمه جست‌وجو استفاده کنید.');
            } finally {
                if (requestVersion === version) input.removeAttribute('aria-busy');
            }
        }, 300);
    }

    input.addEventListener('input', schedule);
    input.addEventListener('focus', schedule);
    input.addEventListener('compositionstart', () => { composing = true; close(); });
    input.addEventListener('compositionend', () => { composing = false; schedule(); });
    form.addEventListener('submit', close);
    document.addEventListener('pointerdown', event => { if (!form.contains(event.target)) close(); });
    form.addEventListener('focusout', event => { if (!form.contains(event.relatedTarget)) close(); });
    form.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            // Focus before closing, so the focus handler cannot reopen the panel.
            if (panel.contains(document.activeElement)) input.focus();
            close();
            event.preventDefault();
        }
        if (panel.hidden || !['ArrowDown', 'ArrowUp'].includes(event.key)) return;
        const links = [...list.querySelectorAll('a')];
        if (!links.length) return;
        const index = links.indexOf(document.activeElement);
        const next = event.key === 'ArrowDown' ? (index + 1) % links.length : (index <= 0 ? links.length - 1 : index - 1);
        event.preventDefault();
        links[next].focus();
    });
});
