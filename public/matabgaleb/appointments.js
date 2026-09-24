(function () {
    'use strict';
    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('appointment-form');
        if (!form) return;

        var phone = document.getElementById('appointment-phone');
        var name = document.getElementById('appointment-name');
        var lastName = document.getElementById('appointment-last-name');
        var userId = document.getElementById('appointment-user-id');
        var status = document.getElementById('patient-search-status');
        var match = document.getElementById('patient-match');
        var matchOptions = document.getElementById('patient-match-options');
        var initialUserId = userId.value;
        var timer;
        var requestController;

        function englishDigits(value) {
            return value.replace(/[۰-۹٠-٩]/g, function (digit) {
                return '۰۱۲۳۴۵۶۷۸۹'.indexOf(digit) > -1 ? '۰۱۲۳۴۵۶۷۸۹'.indexOf(digit) : '٠١٢٣٤٥٦٧٨٩'.indexOf(digit);
            }).replace(/\D/g, '').slice(0, 11);
        }
        function clearMatch(message) {
            if (userId.value && !initialUserId) {
                name.value = '';
                lastName.value = '';
            }
            userId.value = '';
            match.hidden = true;
            matchOptions.innerHTML = '';
            status.textContent = message;
            status.className = '';
        }
        async function lookup() {
            clearTimeout(timer);
            var value = englishDigits(phone.value);
            phone.value = value;
            clearMatch(value.length < 4 ? 'با ورود حداقل ۴ رقم، پرونده‌های مرتبط پیشنهاد می‌شوند.' : 'در حال جست‌وجوی پرونده‌ها…');
            if (value.length < 4) return;
            timer = setTimeout(async function () {
                if (requestController) requestController.abort();
                requestController = new AbortController();
                try {
                    var response = await fetch(form.dataset.patientUrl + '?phone=' + encodeURIComponent(value), {headers: {'Accept': 'application/json'}, signal: requestController.signal});
                    var data = await response.json();
                    if (!data.found) {
                        clearMatch(value.length === 11 ? 'پرونده‌ای با این شماره پیدا نشد؛ هنگام ثبت نوبت یک پرونده جدید ساخته می‌شود.' : 'پرونده مرتبطی پیدا نشد.');
                        status.className = 'is-neutral';
                        return;
                    }
                    data.users.forEach(function (patient) {
                        var option = document.createElement('label');
                        option.className = 'patient-option';
                        var radio = document.createElement('input');
                        radio.type = 'radio'; radio.name = 'patient-match-choice'; radio.value = patient.id;
                        radio.checked = (data.exact && data.users.length === 1) || String(initialUserId) === String(patient.id);
                        var details = document.createElement('span');
                        details.innerHTML = '<strong></strong><small></small>';
                        details.querySelector('strong').textContent = patient.name;
                        details.querySelector('small').textContent = (patient.phone || '') + ' · شماره پرونده: ' + (patient.case_number || '—');
                        var link = document.createElement('a');
                        link.href = patient.url; link.target = '_blank'; link.rel = 'noopener'; link.textContent = 'مشاهده پرونده';
                        option.appendChild(radio); option.appendChild(details); option.appendChild(link);
                        function selectPatient() {
                            phone.value = englishDigits(String(patient.phone || phone.value));
                            userId.value = patient.id;
                            name.value = patient.first_name || '';
                            lastName.value = patient.last_name || '';
                        }
                        radio.addEventListener('change', selectPatient);
                        if (radio.checked) selectPatient();
                        matchOptions.appendChild(option);
                    });
                    initialUserId = '';
                    match.hidden = false;
                    status.textContent = data.exact && data.users.length === 1 ? 'این نوبت به پرونده پیدا‌شده متصل می‌شود.' : 'از بین پرونده‌های پیشنهادی، بیمار را انتخاب کنید.';
                    status.className = 'is-found';
                } catch (error) {
                    if (error.name !== 'AbortError') clearMatch('بررسی پرونده ممکن نشد؛ می‌توانید نوبت را ثبت کنید.');
                }
            }, 300);
        }
        phone.addEventListener('input', lookup);
        if (phone.value) lookup();

        var month = document.getElementById('appointment-month');
        var dayInput = document.getElementById('appointment-day');
        var grid = document.getElementById('appointment-days');
        var daysByMonth = JSON.parse(form.dataset.monthDays || '{}');
        var weekdaysByMonth = JSON.parse(form.dataset.monthWeekdays || '{}');
        function renderDays() {
            var count = Number(daysByMonth[month.value] || 31);
            var selected = Number(dayInput.value);
            if (selected > count) { selected = 0; dayInput.value = ''; }
            grid.innerHTML = '';
            for (var day = 1; day <= count; day++) {
                var weekday = (weekdaysByMonth[month.value] || {})[day] || '';
                var button = document.createElement('button');
                button.type = 'button'; button.className = 'day-button' + (weekday === 'جمعه' ? ' is-friday' : '');
                button.innerHTML = '<strong>' + day + '</strong><small>' + weekday + '</small>';
                button.setAttribute('aria-label', 'روز ' + day + '، ' + weekday);
                if (day === selected) { button.classList.add('is-selected'); button.setAttribute('aria-pressed', 'true'); }
                else button.setAttribute('aria-pressed', 'false');
                button.addEventListener('click', function () {
                    grid.querySelectorAll('.day-button').forEach(function (item) { item.classList.remove('is-selected'); item.setAttribute('aria-pressed', 'false'); });
                    this.classList.add('is-selected'); this.setAttribute('aria-pressed', 'true'); dayInput.value = this.querySelector('strong').textContent;
                });
                grid.appendChild(button);
            }
        }
        month.addEventListener('change', renderDays);
        renderDays();
        form.addEventListener('submit', function (event) {
            if (!dayInput.value) { event.preventDefault(); grid.focus(); alert('لطفاً روز نوبت را انتخاب کنید.'); }
        });
    });
})();
