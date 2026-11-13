/* Modal create/edit for simple admin modules.
 * <button data-crud-create data-action="store-url" data-title="Add branch">
 * <button data-crud-edit data-action="update-url" data-title="Edit branch" data-record='{"name_en":"..."}'>
 */
(function () {
    const modalEl = document.getElementById('crudModal');
    if (!modalEl) return;

    const form = modalEl.querySelector('form');
    const titleEl = modalEl.querySelector('[data-crud-title]');
    const field = (name) => form.querySelector(`input[name="${name}"]`);
    const modal = () => bootstrap.Modal.getOrCreateInstance(modalEl);

    function fill(record) {
        form.querySelectorAll('[name]').forEach((el) => {
            if (el.type === 'hidden' || el.type === 'file' || el.name.startsWith('_')) return;
            const key = el.name.replace(/\[\]$/, '');
            const has = Object.prototype.hasOwnProperty.call(record, key);
            const value = has ? record[key] : (el.dataset.default ?? '');

            if (el.type === 'checkbox') {
                el.checked = has ? (value === true || value === 1 || value === '1') : el.dataset.default === '1';
            } else if (el.multiple) {
                const values = (Array.isArray(value) ? value : []).map(String);
                [...el.options].forEach((o) => { o.selected = values.includes(o.value); });
            } else {
                el.value = value ?? '';
            }
            el.classList.remove('is-invalid');
            el.dispatchEvent(new Event('change', { bubbles: true }));
        });
        form.querySelectorAll('.invalid-feedback').forEach((e) => e.remove());
        form.querySelectorAll('.alert-danger').forEach((e) => e.remove());
    }

    document.addEventListener('click', (event) => {
        const btn = event.target.closest('[data-crud-create], [data-crud-edit]');
        if (!btn) return;
        event.preventDefault();
        const isEdit = btn.hasAttribute('data-crud-edit');

        form.action = btn.dataset.action;
        field('_form_action').value = btn.dataset.action;
        field('_method').value = isEdit ? 'PUT' : 'POST';
        field('_form_title').value = btn.dataset.title || '';
        titleEl.textContent = btn.dataset.title || '';
        fill(isEdit ? JSON.parse(btn.dataset.record || '{}') : {});
        modal().show();
    });

    // Validation failed on the server: reopen with the old input already rendered by Blade.
    if (modalEl.dataset.reopen === '1') {
        document.addEventListener('DOMContentLoaded', () => modal().show());
        if (document.readyState !== 'loading') modal().show();
    }
})();
