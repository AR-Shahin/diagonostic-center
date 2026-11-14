@extends('admin.layouts.master')

@section('title', 'Permissions · '.$role->name)

@push('css')
    <style>
        /* the layout's .app-main scrolls on its own, which would stop the sticky side panel */
        .app-main { overflow: visible !important; }
        .card:hover { transform: none; }

        .perm-aside { position: sticky; top: 1rem; }
        .perm-summary .big { font-size: 2rem; font-weight: 700; line-height: 1; font-variant-numeric: tabular-nums; }
        .perm-summary .progress { height: 6px; }

        .area-nav .nav-link { display: flex; align-items: center; gap: .6rem; padding: .5rem .75rem; border-radius: .5rem; color: var(--bs-body-color); }
        .area-nav .nav-link i { width: 1.1rem; text-align: center; color: var(--bs-secondary-color); }
        .area-nav .nav-link:hover { background: var(--bs-tertiary-bg); }
        .area-nav .nav-link.active { background: var(--bs-primary-bg-subtle); color: var(--bs-primary-text-emphasis); font-weight: 600; }
        .area-nav .nav-link.active i { color: inherit; }
        .area-nav .count { margin-left: auto; font-size: .75rem; font-variant-numeric: tabular-nums; color: var(--bs-secondary-color); }
        .area-nav .count.full { color: var(--bs-success-text-emphasis); font-weight: 600; }

        .perm-toolbar { position: sticky; top: 0; z-index: 5; background: var(--bs-body-bg); border-bottom: 1px solid var(--bs-border-color); }
        .filter-seg .btn { min-width: 5.5rem; }

        .area-section { scroll-margin-top: 5.5rem; }
        .area-head { display: flex; align-items: center; gap: .85rem; padding: 1rem 1.25rem; border-bottom: 1px solid var(--bs-border-color); }
        .area-icon { width: 2.4rem; height: 2.4rem; border-radius: .65rem; display: grid; place-items: center; background: var(--bs-primary-bg-subtle); color: var(--bs-primary-text-emphasis); flex-shrink: 0; }
        .area-head h2 { font-size: 1.05rem; margin: 0; font-weight: 600; }
        .area-head p { margin: 0; font-size: .82rem; color: var(--bs-secondary-color); }

        .module-row { display: grid; grid-template-columns: minmax(12rem, 16rem) 1fr; gap: .75rem 1.25rem; align-items: center; padding: .9rem 1.25rem; border-bottom: 1px solid var(--bs-border-color-translucent); transition: background-color .15s; }
        .module-row:last-child { border-bottom: 0; }
        .module-row:hover { background: var(--bs-tertiary-bg); }
        .module-row .form-check-input { width: 1.15em; height: 1.15em; cursor: pointer; }
        .module-name { font-weight: 600; font-size: .95rem; cursor: pointer; }
        .module-desc { font-size: .8rem; color: var(--bs-secondary-color); margin-left: 1.9rem; }
        @media (max-width: 767.98px) { .module-row { grid-template-columns: 1fr; } }

        .pill-list { display: flex; flex-wrap: wrap; gap: .5rem; }
        .perm-pill input { position: absolute; opacity: 0; pointer-events: none; }
        .perm-pill label {
            display: inline-flex; align-items: center; gap: .4rem; padding: .35rem .8rem .35rem .45rem; margin: 0;
            border: 1px solid var(--bs-border-color); border-radius: 999px; background: var(--bs-body-bg);
            font-size: .85rem; color: var(--bs-body-color); cursor: pointer; user-select: none; position: relative;
            transition: background-color .15s, border-color .15s, color .15s, box-shadow .15s;
        }
        .perm-pill label:hover { border-color: var(--bs-primary-border-subtle); }
        .perm-pill .tick { width: 1.15rem; height: 1.15rem; border-radius: 50%; border: 1.5px solid var(--bs-border-color); display: grid; place-items: center; font-size: .6rem; color: transparent; transition: all .15s; }
        .perm-pill .lock { font-size: .7rem; color: var(--bs-warning-text-emphasis); opacity: .8; }
        .perm-pill input:checked + label { background: var(--bs-primary-bg-subtle); border-color: var(--bs-primary); color: var(--bs-primary-text-emphasis); font-weight: 500; }
        .perm-pill input:checked + label .tick { background: var(--bs-primary); border-color: var(--bs-primary); color: #fff; }
        .perm-pill input:focus-visible + label { box-shadow: 0 0 0 .2rem rgba(var(--bs-primary-rgb), .3); }
        .perm-pill.sensitive input:checked + label { background: var(--bs-warning-bg-subtle); border-color: var(--bs-warning); color: var(--bs-warning-text-emphasis); }
        .perm-pill.sensitive input:checked + label .tick { background: var(--bs-warning); border-color: var(--bs-warning); color: #000; }
        .perm-pill label.changed::after { content: ""; position: absolute; top: -3px; right: -3px; width: .55rem; height: .55rem; border-radius: 50%; background: var(--bs-info); box-shadow: 0 0 0 2px var(--bs-body-bg); }

        .save-bar { position: sticky; bottom: 0; z-index: 6; margin-top: 1rem; padding: .85rem 1.25rem; background: var(--bs-body-bg); border: 1px solid var(--bs-border-color); border-radius: .75rem; box-shadow: 0 -.5rem 1.5rem rgba(0, 0, 0, .08); display: flex; flex-wrap: wrap; align-items: center; gap: .75rem; }
        .save-bar.dirty { border-color: var(--bs-primary); }
        .save-bar .state { display: flex; align-items: center; gap: .6rem; font-size: .9rem; }
        .save-bar .dot { width: .6rem; height: .6rem; border-radius: 50%; background: var(--bs-success); }
        .save-bar.dirty .dot { background: var(--bs-info); animation: pulse 1.6s infinite; }
        @keyframes pulse { 50% { opacity: .35; } }
        @media (prefers-reduced-motion: reduce) { .save-bar.dirty .dot { animation: none; } }

        @media (max-width: 1199.98px) {
            .perm-aside { position: static; }
            .perm-summary .card-body { padding: .75rem 1rem; }
            .perm-summary .big { font-size: 1.4rem; }
        }
        .perm-toolbar { border-radius: .75rem; box-shadow: 0 .25rem .75rem rgba(0, 0, 0, .04); }
        .empty-search { display: none; }
        .no-results .empty-search { display: block; }
    </style>
@endpush

@section('master_content')
    <x-admin.page-header :title="$role->name" icon="fas fa-user-shield"
        :subtitle="'Choose what staff with this role can do. '.($staffCount === 1 ? '1 staff member has' : $staffCount.' staff members have').' this role.'">
        <a class="btn btn-sm btn-light" href="{{ route('admin.roles.index') }}"><i class="fas fa-arrow-left me-1"></i> All roles</a>
    </x-admin.page-header>

    @if ($errors->any())
        <div class="alert alert-danger d-flex gap-2"><i class="fas fa-exclamation-circle mt-1"></i><div>{{ $errors->first() }}</div></div>
    @endif
    @if ($isOwnRole)
        <div class="alert alert-info d-flex gap-2 py-2 small"><i class="fas fa-info-circle mt-1"></i><div>You have this role yourself. Changes apply to you too as soon as you save.</div></div>
    @endif
    @unless ($canSave)
        <div class="alert alert-secondary d-flex gap-2 py-2 small"><i class="fas fa-eye mt-1"></i><div>You can look but not change. Saving needs the “Roles · Create roles &amp; set permissions” permission.</div></div>
    @endunless

    @php
        $totalPerms = collect($areas)->sum(fn ($a) => collect($a['modules'])->sum(fn ($m) => count($m['actions'])));
    @endphp

    <form method="POST" action="{{ route('admin.roles.assign__permission', $role->id) }}" id="permForm">
        @csrf
        <div class="row g-3">
            {{-- ---------- side panel ---------- --}}
            <div class="col-xl-3">
                <div class="perm-aside">
                    <div class="card mb-3 perm-summary">
                        <div class="card-body">
                            <div class="small text-muted mb-1">Allowed</div>
                            <p class="mb-2 text-nowrap"><span class="big" data-total-checked>0</span> <span class="text-muted">of {{ $totalPerms }}</span></p>
                            <div class="progress" role="progressbar" aria-label="Share of permissions allowed" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar" data-total-bar style="width: 0%"></div>
                            </div>
                        </div>
                    </div>

                    <nav class="card mb-3 d-none d-xl-block" aria-label="Permission groups">
                        <div class="card-body p-2 area-nav nav flex-column">
                            @foreach ($areas as $key => $area)
                                <a class="nav-link" href="#area-{{ $key }}" data-nav="{{ $key }}">
                                    <i class="{{ $area['icon'] }}"></i><span>{{ $area['label'] }}</span><span class="count" data-area-count="{{ $key }}"></span>
                                </a>
                            @endforeach
                        </div>
                    </nav>

                    @if ($canSave)
                        <div class="card d-none d-xl-block">
                            <div class="card-body">
                                <div class="small fw-semibold mb-2">Quick start</div>
                                @if ($otherRoles->isNotEmpty())
                                    <label class="form-label small text-muted mb-1" for="copyFrom">Copy from another role</label>
                                    <div class="input-group input-group-sm mb-3">
                                        <select class="form-select" id="copyFrom">
                                            <option value="">Choose a role…</option>
                                            @foreach ($otherRoles as $id => $other)
                                                <option value="{{ $id }}">{{ $other['name'] }} ({{ count($other['permissions']) }})</option>
                                            @endforeach
                                        </select>
                                        <button class="btn btn-outline-primary" type="button" data-copy-from-select>Copy</button>
                                    </div>
                                @endif
                                <div class="d-grid gap-2">
                                    <button class="btn btn-sm btn-light text-start" type="button" data-bulk="view"><i class="fas fa-eye fa-fw me-1"></i> Allow every “view”</button>
                                    <button class="btn btn-sm btn-light text-start" type="button" data-bulk="all"><i class="fas fa-check-double fa-fw me-1"></i> Allow everything</button>
                                    <button class="btn btn-sm btn-light text-start text-danger" type="button" data-bulk="none"><i class="fas fa-times fa-fw me-1"></i> Clear everything</button>
                                </div>
                                <p class="form-text mb-0 mt-2">Nothing changes until you press <b>Save</b>.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ---------- permission list ---------- --}}
            <div class="col-xl-9" id="permMain">
                <div class="card mb-3 perm-toolbar">
                    <div class="card-body py-2 d-flex flex-wrap gap-2 align-items-center">
                        <div class="input-group input-group-sm flex-grow-1" style="max-width: 26rem">
                            <span class="input-group-text bg-transparent"><i class="fas fa-search text-muted"></i></span>
                            <input type="search" class="form-control" id="permSearch" placeholder="Search, e.g. refund, verify, expenses" aria-label="Search permissions" autocomplete="off">
                        </div>
                        <div class="btn-group btn-group-sm filter-seg ms-md-auto" role="group" aria-label="Show">
                            <input type="radio" class="btn-check" name="show" id="showAll" value="all" checked><label class="btn btn-outline-primary" for="showAll">All</label>
                            <input type="radio" class="btn-check" name="show" id="showOn" value="on"><label class="btn btn-outline-primary" for="showOn">Allowed</label>
                            <input type="radio" class="btn-check" name="show" id="showOff" value="off"><label class="btn btn-outline-primary" for="showOff">Not allowed</label>
                        </div>
                        @if ($canSave)
                            <div class="dropdown d-xl-none">
                                <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-magic me-1"></i> Quick start</button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><button class="dropdown-item" type="button" data-bulk="view"><i class="fas fa-eye fa-fw me-1"></i> Allow every “view”</button></li>
                                    <li><button class="dropdown-item" type="button" data-bulk="all"><i class="fas fa-check-double fa-fw me-1"></i> Allow everything</button></li>
                                    <li><button class="dropdown-item text-danger" type="button" data-bulk="none"><i class="fas fa-times fa-fw me-1"></i> Clear everything</button></li>
                                    @if ($otherRoles->isNotEmpty())
                                        <li><hr class="dropdown-divider"></li>
                                        <li><h6 class="dropdown-header">Copy from role</h6></li>
                                        @foreach ($otherRoles as $id => $other)
                                            <li><button class="dropdown-item" type="button" data-copy-id="{{ $id }}">{{ $other['name'] }} <span class="text-muted small">({{ count($other['permissions']) }})</span></button></li>
                                        @endforeach
                                    @endif
                                </ul>
                            </div>
                        @endif
                        <span class="small text-muted d-none d-lg-inline"><i class="fas fa-lock text-warning me-1"></i> = sensitive (money, sign-off, access)</span>
                    </div>
                </div>

                @foreach ($areas as $key => $area)
                    <section class="card mb-3 area-section" id="area-{{ $key }}" data-area="{{ $key }}" aria-labelledby="area-title-{{ $key }}">
                        <div class="area-head">
                            <span class="area-icon"><i class="{{ $area['icon'] }}"></i></span>
                            <div class="flex-grow-1">
                                <h2 id="area-title-{{ $key }}">{{ $area['label'] }}</h2>
                                <p>{{ $area['description'] }}</p>
                            </div>
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" role="switch" id="areaAll-{{ $key }}" data-area-toggle="{{ $key }}" @disabled(! $canSave)>
                                <label class="form-check-label small text-muted" for="areaAll-{{ $key }}">All</label>
                            </div>
                        </div>
                        @foreach ($area['modules'] as $module)
                            <div class="module-row" data-module="{{ $module['key'] }}"
                                data-search="{{ strtolower($area['label'].' '.$module['label'].' '.$module['description'].' '.collect($module['actions'])->pluck('label')->implode(' ').' '.collect($module['actions'])->pluck('name')->implode(' ')) }}">
                                <div>
                                    <div class="form-check m-0">
                                        <input class="form-check-input" type="checkbox" id="mod-{{ $module['key'] }}" data-module-toggle="{{ $module['key'] }}" aria-label="All {{ $module['label'] }} permissions" @disabled(! $canSave)>
                                        <label class="module-name ms-1" for="mod-{{ $module['key'] }}">{{ $module['label'] }}</label>
                                    </div>
                                    @if ($module['description'])<div class="module-desc">{{ $module['description'] }}</div>@endif
                                </div>
                                <div class="pill-list">
                                    @foreach ($module['actions'] as $action)
                                        <div @class(['perm-pill', 'sensitive' => $action['sensitive']])>
                                            <input type="checkbox" name="permissions[]" value="{{ $action['id'] }}" id="perm-{{ $action['id'] }}"
                                                data-perm data-action="{{ $action['action'] }}" @checked(in_array($action['id'], $alreadyGiven)) @disabled(! $canSave)>
                                            <label for="perm-{{ $action['id'] }}" title="{{ $action['name'] }}">
                                                <span class="tick"><i class="fas fa-check"></i></span>
                                                {{ $action['label'] }}
                                                @if ($action['sensitive'])<i class="fas fa-lock lock" aria-label="sensitive"></i>@endif
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </section>
                @endforeach

                <div class="card empty-search mb-3">
                    <div class="card-body text-center text-muted py-5"><i class="fas fa-search fa-2x mb-2 d-block opacity-50"></i>Nothing matches. Try another word.</div>
                </div>

                @if ($canSave)
                    <div class="save-bar" data-save-bar>
                        <div class="state"><span class="dot" aria-hidden="true"></span><span data-change-text role="status" aria-live="polite">All changes saved</span></div>
                        <div class="ms-auto d-flex gap-2">
                            <button type="button" class="btn btn-light" data-discard disabled>Discard</button>
                            <button type="submit" class="btn btn-primary px-4" data-save disabled><i class="fas fa-save me-1"></i> Save</button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </form>
@endsection

@push('script')
    <script>
        (() => {
            const form = document.getElementById('permForm');
            const perms = [...form.querySelectorAll('[data-perm]')];
            const total = perms.length;
            const saved = new Set(perms.filter((p) => p.checked).map((p) => p.value));
            const otherRoles = {{ \Illuminate\Support\Js::from($otherRoles) }};
            const inModule = (key) => perms.filter((p) => p.closest('.module-row').dataset.module === key);
            const inArea = (key) => perms.filter((p) => p.closest('[data-area]').dataset.area === key);

            const setState = (box, list) => {
                const on = list.filter((p) => p.checked).length;
                box.checked = on === list.length && on > 0;
                box.indeterminate = on > 0 && on < list.length;
            };

            function refresh() {
                const on = perms.filter((p) => p.checked);
                document.querySelector('[data-total-checked]').textContent = on.length;
                document.querySelector('[data-total-bar]').style.width = (total ? on.length / total * 100 : 0) + '%';

                document.querySelectorAll('[data-module-toggle]').forEach((box) => setState(box, inModule(box.dataset.moduleToggle)));
                document.querySelectorAll('[data-area-toggle]').forEach((box) => setState(box, inArea(box.dataset.areaToggle)));
                document.querySelectorAll('[data-area-count]').forEach((el) => {
                    const list = inArea(el.dataset.areaCount);
                    const n = list.filter((p) => p.checked).length;
                    el.textContent = n + '/' + list.length;
                    el.classList.toggle('full', n === list.length);
                });

                let added = 0; let removed = 0;
                perms.forEach((p) => {
                    const changed = p.checked !== saved.has(p.value);
                    p.nextElementSibling.classList.toggle('changed', changed);
                    if (changed) p.checked ? added++ : removed++;
                });
                const dirty = added + removed > 0;
                const bar = document.querySelector('[data-save-bar]');
                if (bar) {
                    bar.classList.toggle('dirty', dirty);
                    const parts = [];
                    if (added) parts.push('<b>' + added + '</b> to allow');
                    if (removed) parts.push('<b>' + removed + '</b> to remove');
                    bar.querySelector('[data-change-text]').innerHTML = dirty ? 'Unsaved: ' + parts.join(', ') : 'All changes saved';
                    bar.querySelector('[data-save]').disabled = !dirty;
                    bar.querySelector('[data-discard]').disabled = !dirty;
                }
                form.dataset.dirty = dirty ? '1' : '';
                applyFilter();
            }

            // ---- toggles
            form.addEventListener('change', (e) => {
                const t = e.target;
                if (t.dataset.moduleToggle) inModule(t.dataset.moduleToggle).forEach((p) => { p.checked = t.checked; });
                if (t.dataset.areaToggle) inArea(t.dataset.areaToggle).filter((p) => !p.closest('.module-row').hidden).forEach((p) => { p.checked = t.checked; });
                if (t.name === 'show' || t.matches('[data-perm], [data-module-toggle], [data-area-toggle]')) refresh();
            });

            document.querySelectorAll('[data-bulk]').forEach((btn) => btn.addEventListener('click', () => {
                const mode = btn.dataset.bulk;
                perms.forEach((p) => {
                    if (mode === 'all') p.checked = true;
                    else if (mode === 'none') p.checked = false;
                    else if (mode === 'view' && p.dataset.action === 'view') p.checked = true;
                });
                refresh();
            }));

            const copyRole = (id) => {
                const role = otherRoles[id];
                if (!role) return;
                const ids = new Set(role.permissions.map(String));
                perms.forEach((p) => { p.checked = ids.has(p.value); });
                refresh();
            };
            document.querySelector('[data-copy-from-select]')?.addEventListener('click', () => copyRole(document.getElementById('copyFrom').value));
            document.querySelectorAll('[data-copy-id]').forEach((btn) => btn.addEventListener('click', () => copyRole(btn.dataset.copyId)));

            document.querySelector('[data-discard]')?.addEventListener('click', () => {
                perms.forEach((p) => { p.checked = saved.has(p.value); });
                refresh();
            });

            // ---- search + show filter
            const search = document.getElementById('permSearch');
            function applyFilter() {
                const q = search.value.trim().toLowerCase();
                const show = form.querySelector('[name=show]:checked').value;
                let any = false;
                document.querySelectorAll('[data-area]').forEach((section) => {
                    let visibleRows = 0;
                    section.querySelectorAll('.module-row').forEach((row) => {
                        const boxes = [...row.querySelectorAll('[data-perm]')];
                        const okText = !q || row.dataset.search.includes(q);
                        const okShow = show === 'all' || boxes.some((b) => (show === 'on' ? b.checked : !b.checked));
                        row.hidden = !(okText && okShow);
                        if (!row.hidden) visibleRows++;
                    });
                    section.hidden = visibleRows === 0;
                    if (visibleRows) any = true;
                });
                document.getElementById('permMain').classList.toggle('no-results', !any);
            }
            search.addEventListener('input', applyFilter);
            search.addEventListener('keydown', (e) => { if (e.key === 'Enter') e.preventDefault(); });

            // ---- side navigation follows the scroll
            const links = [...document.querySelectorAll('[data-nav]')];
            if ('IntersectionObserver' in window && links.length) {
                const io = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (!entry.isIntersecting) return;
                        links.forEach((l) => l.classList.toggle('active', l.dataset.nav === entry.target.dataset.area));
                    });
                }, { rootMargin: '-20% 0px -70% 0px' });
                document.querySelectorAll('[data-area]').forEach((s) => io.observe(s));
            }

            // ---- don't lose work
            window.addEventListener('beforeunload', (e) => { if (form.dataset.dirty) { e.preventDefault(); e.returnValue = ''; } });
            form.addEventListener('submit', () => {
                form.dataset.dirty = '';
                const btn = form.querySelector('[data-save]');
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving…';
            });

            refresh();
        })();
    </script>
@endpush
