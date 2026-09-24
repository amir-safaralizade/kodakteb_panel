document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.querySelector('.app-sidebar');
    const toggle = document.querySelector('.menu-toggle');
    const overlay = document.querySelector('.sidebar-overlay');
    const mobile = window.matchMedia('(max-width: 991px)');
    const main = document.querySelector('.mainsection');
    function setMenu(open, restoreFocus = true) {
        document.body.classList.toggle('nav-open', open);
        toggle?.setAttribute('aria-expanded', String(open));
        if (overlay) overlay.hidden = !open;
        if (main) main.inert = open;
        if (sidebar) sidebar.inert = mobile.matches && !open;
        if (open) sidebar?.querySelector('.sidebar-close')?.focus();
        else if (restoreFocus && mobile.matches) toggle?.focus();
    }
    toggle?.addEventListener('click', () => setMenu(true));
    document.querySelector('.sidebar-close')?.addEventListener('click', () => setMenu(false));
    overlay?.addEventListener('click', () => setMenu(false));
    mobile.addEventListener('change', () => setMenu(false, false));
    setMenu(false, false);
    document.addEventListener('keydown', event => {
        if (!document.body.classList.contains('nav-open')) return;
        if (event.key === 'Escape') setMenu(false);
        if (event.key === 'Tab') {
            const focusable = [...sidebar.querySelectorAll('a[href], button')].filter(el => el.getClientRects().length);
            const first = focusable[0], last = focusable[focusable.length - 1];
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
            if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
        }
    });
    document.querySelectorAll('.nav-link.is-active').forEach(link => link.setAttribute('aria-current', 'page'));
    document.querySelectorAll('[data-today]').forEach(element => {
        element.textContent = new Intl.DateTimeFormat('fa-IR', { dateStyle: 'long', timeZone: 'Asia/Tehran' }).format(new Date());
        element.dateTime = new Date().toISOString();
    });
    document.querySelector('.password-toggle')?.addEventListener('click', event => {
        const button = event.currentTarget;
        const input = document.getElementById(button.getAttribute('aria-controls'));
        const visible = input.type === 'password';
        input.type = visible ? 'text' : 'password';
        button.textContent = visible ? 'پنهان' : 'نمایش';
        button.setAttribute('aria-pressed', String(visible));
    });
    document.querySelectorAll('button.accordion').forEach((button, index) => {
        const panel = button.nextElementSibling;
        if (!panel) return;
        panel.id ||= 'visit-panel-' + index;
        button.type = 'button';
        button.setAttribute('aria-controls', panel.id);
        button.setAttribute('aria-expanded', 'false');
        panel.hidden = true;
        button.addEventListener('click', () => {
            const open = button.getAttribute('aria-expanded') !== 'true';
            button.setAttribute('aria-expanded', String(open));
            panel.hidden = !open;
            panel.style.height = open ? 'auto' : '';
        });
    });
    // Keep labels visible when a field has a value, including older forms.
    const names = { insurance: 'بیمه', insurance_id: 'بیمه', sex: 'جنسیت', raveshdaryaft: 'روش دریافت', hazine: 'هزینه (ریال)' };
    document.querySelectorAll('.form-groups input:not([type="hidden"]), .form-groups select, .form-groups textarea').forEach((input, index) => {
        input.id ||= 'form-field-' + index;
        const parent = input.parentElement;
        const existing = [...parent.children].find(el => el.tagName === 'LABEL');
        if (existing && !parent.classList.contains('flex-form-group')) {
            existing.htmlFor = input.id;
        } else {
            const text = names[input.name] || input.getAttribute('placeholder') || input.name;
            const wrapper = document.createElement('div');
            wrapper.className = 'field';
            const label = document.createElement('label');
            label.htmlFor = input.id;
            label.textContent = text.trim();
            input.before(wrapper);
            wrapper.append(label, input);
        }
    });
    document.querySelectorAll('table .btn').forEach(control => {
        if (control.textContent.trim()) return;
        const title = control.querySelector('.bi-trash') ? 'حذف' : control.querySelector('.bi-pencil-square') ? 'ویرایش' : control.querySelector('.bi-eye-fill') ? 'مشاهده جزئیات' : null;
        if (title) { control.setAttribute('aria-label', title); control.title = title; }
    });
});
function itpro(value) {
    const parts = String(value).replace(/,/g, '').split('.');
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    return parts.join('.');
}
function separate(value) { return itpro(value); }
