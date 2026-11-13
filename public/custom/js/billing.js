/* Billing counter (admin/invoices/create).
 * The numbers shown here are a preview; the server recalculates everything from the database on save.
 */
(function () {
    'use strict';

    const cfg = window.POS;
    const $ = (sel, root = document) => root.querySelector(sel);
    const form = $('#billForm');
    const money = (v) => '৳ ' + Number(v || 0).toLocaleString('en-US', { maximumFractionDigits: 2 });
    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const notify = (msg, type = 'warning') => (window.toastr ? toastr[type](msg) : alert(msg));

    const state = { cart: [], patient: null, referrer: null, paidTouched: false };

    /* ---------- Generic typeahead ---------- */
    function typeahead({ input, list, url, params = () => ({}), render, onPick, minLength = 1, emptyText = 'No match' }) {
        let timer; let rows = []; let active = -1; let lastQ = null;

        const close = () => { list.hidden = true; active = -1; };
        const highlight = () => list.querySelectorAll('.ta-item').forEach((el, i) => el.classList.toggle('is-active', i === active));
        const show = () => {
            list.innerHTML = rows.length
                ? rows.map((r, i) => `<div class="ta-item" data-i="${i}">${render(r)}</div>`).join('')
                : `<div class="ta-empty">${esc(emptyText)}</div>`;
            list.hidden = false; active = rows.length ? 0 : -1; highlight();
        };
        const load = async () => {
            const q = input.value.trim();
            if (q.length < minLength) { close(); return; }
            lastQ = q;
            const qs = new URLSearchParams({ q, ...params() });
            const res = await fetch(`${url}?${qs}`, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok || lastQ !== q) return;
            rows = await res.json();
            show();
        };
        const pick = (i) => { if (rows[i]) { onPick(rows[i]); input.value = ''; close(); } };

        input.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(load, 180); });
        input.addEventListener('focus', () => { if (input.value.trim().length >= minLength) load(); });
        input.addEventListener('keydown', (e) => {
            if (list.hidden) return;
            if (e.key === 'ArrowDown') { e.preventDefault(); active = Math.min(active + 1, rows.length - 1); highlight(); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); active = Math.max(active - 1, 0); highlight(); }
            else if (e.key === 'Enter') { e.preventDefault(); pick(active); }
            else if (e.key === 'Escape') { close(); }
        });
        list.addEventListener('mousedown', (e) => { const el = e.target.closest('.ta-item'); if (el) { e.preventDefault(); pick(+el.dataset.i); } });
        input.addEventListener('blur', () => setTimeout(close, 150));
        // Enter with an empty dropdown must never submit the bill
        input.addEventListener('keydown', (e) => { if (e.key === 'Enter') e.preventDefault(); });
    }

    /* ---------- Patient ---------- */
    const patientChip = $('#patientChip');
    const newPatient = $('#newPatient');

    function setPatient(p) {
        state.patient = p;
        $('#patientId').value = p ? p.id : '';
        newPatient.hidden = !!p;
        newPatient.querySelectorAll('input').forEach((el) => { el.disabled = !!p; });
        patientChip.hidden = !p;
        if (!p) { patientChip.innerHTML = ''; return; }
        const due = p.due > 0 ? `<span class="badge text-bg-danger ms-2">Previous due ${money(p.due)}</span>` : '';
        patientChip.innerHTML = `
            <div class="patient-chip">
                <span class="patient-avatar"><i class="fas fa-user"></i></span>
                <div class="flex-grow-1">
                    <div class="fw-semibold">${esc(p.name)} <span class="text-muted small">${esc(p.code)}</span>${due}</div>
                    <div class="small text-muted">${esc(p.mobile)} · ${esc({ male: 'Male', female: 'Female', other: 'Other' }[p.gender] || '')} · ${esc(p.age || 'age not set')}</div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="clearPatient">Change</button>
            </div>`;
        $('#clearPatient').addEventListener('click', () => { setPatient(null); $('#pName').focus(); });
    }

    typeahead({
        input: $('#patientSearch'), list: $('#patientResults'), url: cfg.urls.patients, minLength: 2,
        emptyText: 'No patient found — fill the form below to add a new one.',
        render: (p) => `<span><b>${esc(p.name)}</b> <small>${esc(p.code)}</small><br><small>${esc(p.mobile)} · ${esc(p.age || '')}</small></span>`
            + (p.due > 0 ? `<small class="text-danger">Due ${money(p.due)}</small>` : ''),
        onPick: (p) => { setPatient(p); $('#itemSearch').focus(); },
    });

    // Typing a mobile in the new-patient form: one phone = one account, so show who is already on it.
    let mobileTimer;
    $('#pMobile').addEventListener('input', (e) => {
        clearTimeout(mobileTimer);
        const v = e.target.value.replace(/\D/g, '').replace(/^88/, '');
        const hint = $('#mobileHint');
        const family = $('#familyBox');
        if (v.length < 11) { hint.innerHTML = ''; family.hidden = true; return; }
        mobileTimer = setTimeout(async () => {
            const res = await fetch(`${cfg.urls.patients}?q=${encodeURIComponent(v)}`, { headers: { Accept: 'application/json' } });
            const rows = (res.ok ? await res.json() : []).filter((p) => p.mobile === v);
            family.hidden = rows.length === 0;
            if (!rows.length) { $('#asFamily').checked = false; hint.innerHTML = '<span class="text-success">New number — a website account will be created (password = phone number).</span>'; return; }
            hint.innerHTML = `<span class="text-primary">Already registered: ` + rows.map((p, i) => `<a href="#" data-existing="${i}">${esc(p.name)}</a>`).join(', ')
                + ' — click to select, or tick below for a new family member.</span>';
            hint.querySelectorAll('[data-existing]').forEach((a) => a.addEventListener('click', (ev) => { ev.preventDefault(); setPatient(rows[+a.dataset.existing]); }));
        }, 250);
    });

    /* ---------- Referrer ---------- */
    function setReferrer(r) {
        state.referrer = r;
        $('#referrerId').value = r ? r.id : '';
        $('#referrerBox').hidden = !!r;
        const chip = $('#referrerChip');
        chip.hidden = !r;
        chip.innerHTML = r ? `<span class="badge text-bg-light border fs-6 fw-normal">${esc(r.label)} <button type="button" class="btn-close btn-close-sm ms-1" style="font-size:.6rem" aria-label="Remove referrer" id="clearRef"></button></span>` : '';
        if (r) $('#clearRef').addEventListener('click', () => setReferrer(null));
    }
    typeahead({
        input: $('#referrerSearch'), list: $('#referrerResults'), url: cfg.urls.referrers, minLength: 1,
        render: (r) => `<span>${esc(r.label)}<br><small>${esc(r.meta)}</small></span>`,
        onPick: setReferrer,
    });

    /* ---------- Cart ---------- */
    const branchId = () => ($('#f_branch_id') || {}).value;

    function addItem(item) {
        const key = `${item.type}-${item.id}`;
        if (state.cart.some((c) => `${c.type}-${c.id}` === key)) { notify(`${item.name} is already added.`); return; }
        // A test can't be billed twice (alone and inside a package)
        const testIds = new Set(state.cart.flatMap((c) => (c.type === 'test' ? [c.id] : c.tests)));
        const incoming = item.type === 'test' ? [item.id] : item.tests;
        const clash = incoming.find((id) => testIds.has(id));
        if (clash !== undefined) { notify(`${item.name} overlaps with a test already on this bill. Remove one of them.`); return; }
        state.cart.push(item);
        renderCart();
    }

    function renderCart() {
        const body = $('#cartBody');
        body.innerHTML = state.cart.map((c, i) => `
            <tr>
                <td class="text-muted">${i + 1}</td>
                <td>
                    <div class="fw-semibold">${c.type === 'package' ? '<i class="fas fa-box-open text-warning me-1"></i>' : ''}${esc(c.name)} <span class="text-muted small">${esc(c.code)}</span></div>
                    <div class="cart-sub">${c.type === 'package' ? esc((c.test_names || []).join(', ')) : esc(c.sub || '')}</div>
                </td>
                <td class="text-end num">${c.regular > c.price ? `<s class="text-muted small me-1">${money(c.regular)}</s>` : ''}<b>${money(c.price)}</b></td>
                <td class="text-end"><button type="button" class="btn btn-sm btn-link text-danger" data-remove="${i}" aria-label="Remove ${esc(c.name)}"><i class="fas fa-times"></i></button></td>
            </tr>`).join('');
        $('#cartEmpty').hidden = state.cart.length > 0;
        $('#cartCount').textContent = state.cart.length;
        $('#itemInputs').innerHTML = state.cart.map((c, i) =>
            `<input type="hidden" name="items[${i}][type]" value="${c.type}"><input type="hidden" name="items[${i}][id]" value="${c.id}">`).join('');
        recalc();
    }

    $('#cartBody').addEventListener('click', (e) => {
        const btn = e.target.closest('[data-remove]');
        if (btn) { state.cart.splice(+btn.dataset.remove, 1); renderCart(); }
    });

    typeahead({
        input: $('#itemSearch'), list: $('#itemResults'), url: cfg.urls.items, params: () => ({ branch_id: branchId() }),
        render: (r) => `<span>${r.type === 'package' ? '<i class="fas fa-box-open text-warning me-1"></i>' : ''}<b>${esc(r.name)}</b> <small>${esc(r.code)}</small><br><small>${esc(r.sub || '')}</small></span><b class="num">${money(r.price)}</b>`,
        onPick: addItem,
    });
    document.querySelectorAll('[data-quick]').forEach((b) => b.addEventListener('click', () => addItem(JSON.parse(b.dataset.quick))));

    const branchSelect = $('select#f_branch_id');
    if (branchSelect) branchSelect.addEventListener('change', () => {
        if (state.cart.length) notify('Branch changed — check that every test is offered at this branch.', 'info');
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === '/' && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) { e.preventDefault(); $('#itemSearch').focus(); }
    });

    /* ---------- Totals (mirror of InvoiceCalculator) ---------- */
    const r2 = (v) => Math.round(v * 100) / 100;
    let net = 0;

    function recalc() {
        const subtotal = r2(state.cart.reduce((s, c) => s + Number(c.price), 0));
        const dType = form.querySelector('[name="discount_type"]:checked')?.value || 'percent';
        let dValue = parseFloat($('#discountValue').value) || 0;
        let discount = 0;
        if (dValue > 0) discount = dType === 'percent' ? r2(subtotal * Math.min(dValue, 100) / 100) : Math.min(r2(dValue), subtotal);
        const pct = subtotal > 0 ? (discount / subtotal) * 100 : 0;

        const vat = r2((subtotal - discount) * cfg.rules.vat / 100);
        const home = $('#homeCollection').checked ? cfg.rules.home : 0;
        const raw = subtotal - discount + vat + home;
        net = cfg.rules.round ? Math.round(raw) : r2(raw);

        $('#sSubtotal').textContent = money(subtotal);
        $('#sDiscount').textContent = '− ' + money(discount);
        $('#discountLine').hidden = discount <= 0;
        $('#discountExtra').hidden = discount <= 0;
        const needsApproval = pct > cfg.rules.limit + 0.0001;
        $('#approvalBox').hidden = !needsApproval;
        $('#approvalText').textContent = `${pct.toFixed(1)}% is above your limit (${cfg.rules.limit}%). A manager must approve.`;
        if ($('#sVat')) $('#sVat').textContent = money(vat);
        $('#homeLine').hidden = !home; $('#sHome').textContent = money(home);
        const round = r2(net - raw);
        $('#roundLine').hidden = !round; $('#sRound').textContent = (round > 0 ? '+' : '') + round.toFixed(2);
        $('#sNet').textContent = money(net);

        const paidInput = $('#paidAmount');
        if (!state.paidTouched) paidInput.value = net || '';
        const paid = Math.min(parseFloat(paidInput.value) || 0, net);
        const due = r2(net - paid);
        $('#sDue').textContent = money(due);
        $('#sDue').className = 'num fs-5 ' + (due > 0 ? 'text-danger' : 'text-success');
    }

    ['input', 'change'].forEach((ev) => form.addEventListener(ev, (e) => {
        if (e.target.matches('#discountValue, [name="discount_type"], #homeCollection')) recalc();
    }));
    $('#paidAmount').addEventListener('input', () => { state.paidTouched = true; recalc(); });
    $('#payFull').addEventListener('click', () => { state.paidTouched = false; recalc(); });
    $('#payMethod').addEventListener('change', (e) => { $('#trxBox').hidden = e.target.value === 'cash'; });
    $('#trxBox').hidden = $('#payMethod').value === 'cash';

    /* ---------- Submit ---------- */
    let submitting = false;
    form.addEventListener('submit', (e) => {
        const after = e.submitter?.dataset.after || 'show';
        $('#afterAction').value = after;
        if (!state.cart.length) { e.preventDefault(); notify('Add at least one test or package.'); $('#itemSearch').focus(); return; }
        if (!state.patient && !$('#pName').value.trim()) { e.preventDefault(); notify('Enter the patient name or pick an existing patient.'); $('#pName').focus(); return; }
        if ((parseFloat($('#paidAmount').value) || 0) > net) { e.preventDefault(); notify('Paid amount is more than the net payable.'); return; }
        if (submitting) { e.preventDefault(); return; }
        submitting = true; // prevent double bills from double clicks
        form.querySelectorAll('button[type=submit]').forEach((b) => { b.disabled = true; });
    });

    /* ---------- Initial state (after a validation error, or "new bill for this patient") ---------- */
    if (cfg.initial.patient) setPatient(cfg.initial.patient);
    if (cfg.initial.referrer) setReferrer(cfg.initial.referrer);
    state.cart = cfg.initial.cart || [];
    if ($('#paidAmount').value !== '') state.paidTouched = true;
    renderCart();
    (state.patient ? $('#itemSearch') : $('#pName')).focus();
})();
