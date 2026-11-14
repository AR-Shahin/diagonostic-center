/* Diagnostic Center – public website behaviour (no dependencies besides Bootstrap's own JS) */
(() => {
    'use strict';

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ---- Sticky header: show hairline + shadow after scrolling ---- */
    const header = document.getElementById('siteHeader');
    if (header) {
        const onScroll = () => header.classList.toggle('is-scrolled', window.scrollY > 8);
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
    }

    /* ---- Reveal on scroll (elements are visible by default without JS) ---- */
    const revealEls = document.querySelectorAll('.reveal');
    const finishReveal = (el) => {
        // Once shown, drop the helper classes so hover transitions on the element stay snappy.
        const delay = parseFloat(getComputedStyle(el).getPropertyValue('--d')) || 0;
        window.setTimeout(() => el.classList.remove('reveal', 'in'), (0.9 + delay) * 1000 + 150);
    };
    if (reduceMotion || !('IntersectionObserver' in window)) {
        revealEls.forEach((el) => el.classList.add('in'));
    } else {
        const io = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('in');
                finishReveal(entry.target);
                io.unobserve(entry.target);
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -6% 0px' });
        revealEls.forEach((el) => io.observe(el));
    }

    /* ---- Number counters: only animate real numbers greater than zero ---- */
    const counters = document.querySelectorAll('[data-count]');
    const runCounter = (el) => {
        const target = parseInt(el.dataset.count, 10);
        if (!Number.isFinite(target) || target <= 0 || reduceMotion) return;
        const start = performance.now();
        const duration = 1400;
        const step = (now) => {
            const t = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - t, 3);
            el.textContent = Math.round(target * eased).toLocaleString('en-US');
            if (t < 1) requestAnimationFrame(step);
        };
        requestAnimationFrame(step);
    };
    if ('IntersectionObserver' in window) {
        const co = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                runCounter(entry.target);
                co.unobserve(entry.target);
            });
        }, { threshold: 0.6 });
        counters.forEach((el) => co.observe(el));
    }

    /* ---- Live filter for the tests / doctors pages ---- */
    document.querySelectorAll('[data-filter-scope]').forEach((scope) => {
        const input = scope.querySelector('[data-filter-input]');
        const chips = [...scope.querySelectorAll('[data-filter-chip]')];
        const wraps = [...scope.querySelectorAll('[data-filter-wrap]')];
        const count = scope.querySelector('[data-filter-count]');
        const empty = scope.querySelector('[data-filter-empty]');
        let category = (chips.find((c) => c.classList.contains('is-active')) || {}).dataset?.filterChip ?? '';

        const apply = () => {
            const q = (input ? input.value : '').trim().toLowerCase();
            let shown = 0;
            wraps.forEach((wrap) => {
                const item = wrap.querySelector('[data-filter-item]');
                const okName = !q || item.dataset.name.includes(q);
                const okCat = !category || item.dataset.cat === category;
                const visible = okName && okCat;
                wrap.hidden = !visible;
                if (visible) shown += 1;
            });
            if (count) count.textContent = (count.dataset.template || ':n').replace(':n', shown);
            if (empty) empty.hidden = shown !== 0;
        };

        if (input) input.addEventListener('input', apply);
        chips.forEach((chip) => chip.addEventListener('click', () => {
            category = chip.dataset.filterChip;
            chips.forEach((c) => {
                const active = c === chip;
                c.classList.toggle('is-active', active);
                c.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
            apply();
        }));
        apply();
    });

    /* ---- Website forms: bring the success / error message into view after the page reloads ---- */
    const formMessage = document.querySelector('[data-focus-me]');
    if (formMessage) {
        formMessage.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'center' });
        formMessage.focus({ preventScroll: true });
    }

    /* ---- Appointment form: the address is needed only for home collection ---- */
    const apForm = document.getElementById('appointmentForm');
    if (apForm) {
        const address = apForm.querySelector('[name=address]');
        const syncType = () => { address.required = apForm.querySelector('[name=type]:checked')?.value === 'home'; };
        apForm.querySelectorAll('[name=type]').forEach((r) => r.addEventListener('change', syncType));
        syncType();
    }

    /* ---- Demo forms: not connected to a backend yet ---- */
    let toast;
    const showToast = (message) => {
        if (!toast) {
            toast = document.createElement('div');
            toast.className = 'toast-pill';
            toast.setAttribute('role', 'status');
            document.body.appendChild(toast);
        }
        toast.textContent = message;
        toast.classList.add('show');
        window.clearTimeout(showToast.timer);
        showToast.timer = window.setTimeout(() => toast.classList.remove('show'), 3200);
    };
    document.querySelectorAll('[data-demo-form]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            showToast(document.body.dataset.msgForm || '');
        });
    });
    document.querySelectorAll('[data-demo-action]').forEach((btn) => {
        btn.addEventListener('click', () => showToast(document.body.dataset.msgOtp || ''));
    });

    /* ---- Show / hide password ---- */
    document.querySelectorAll('[data-toggle-password]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const input = document.getElementById(btn.dataset.togglePassword);
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.querySelector('i')?.classList.toggle('bi-eye-slash', show);
        });
    });
})();
