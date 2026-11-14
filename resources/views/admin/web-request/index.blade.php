@extends('admin.layouts.master')

@section('title', 'Website requests')

@push('css')
    <style>.card:hover { transform: none; } .req-card .meta { font-size: .85rem; color: var(--bs-secondary-color); }</style>
@endpush

@section('master_content')
    <x-admin.page-header title="Website requests" icon="fas fa-inbox" subtitle="Appointments, home collection, messages and corporate enquiries from the website. Call the person, then mark the request.">
    </x-admin.page-header>

    <div class="d-flex flex-wrap gap-2 mb-3">
        <div class="btn-group btn-group-sm flex-wrap" role="group" aria-label="Type">
            <a @class(['btn btn-outline-primary', 'active' => ! $type]) href="{{ request()->fullUrlWithQuery(['type' => null, 'page' => null]) }}">All</a>
            @foreach (\App\Models\WebRequest::TYPES as $key => [$label, $icon])
                <a @class(['btn btn-outline-primary', 'active' => $type === $key]) href="{{ request()->fullUrlWithQuery(['type' => $key, 'page' => null]) }}"><i class="{{ $icon }} me-1"></i>{{ $label }}
                    @if ($newCounts[$key] ?? 0)<span class="badge text-bg-danger ms-1">{{ $newCounts[$key] }}</span>@endif
                </a>
            @endforeach
        </div>
        <div class="btn-group btn-group-sm ms-md-auto" role="group" aria-label="Status">
            @foreach (['open' => 'To do', 'done' => 'Done', 'cancelled' => 'Cancelled'] as $key => $label)
                <a @class(['btn btn-outline-secondary', 'active' => $status === $key]) href="{{ request()->fullUrlWithQuery(['status' => $key, 'page' => null]) }}">{{ $label }}</a>
            @endforeach
        </div>
    </div>

    @forelse ($requests as $r)
        @php
            [$typeLabel, $typeIcon] = \App\Models\WebRequest::TYPES[$r->type];
        @endphp
        <div @class(['card req-card mb-2', 'border-danger' => $r->status === 'new'])>
            <div class="card-body py-2">
                <div class="row g-2 align-items-center">
                    <div class="col-lg-5">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <i class="{{ $typeIcon }} text-primary" title="{{ $typeLabel }}"></i>
                            <b>{{ $r->name }}</b>
                            <a class="btn btn-sm btn-outline-success py-0" href="tel:{{ $r->phone }}"><i class="fas fa-phone-alt me-1"></i>{{ $r->phone }}</a>
                            {!! $r->statusBadge() !!}
                        </div>
                        <div class="meta mt-1">
                            {{ $typeLabel }} · sent {{ $r->created_at->diffForHumans() }}
                            @if ($r->age) · age {{ $r->age }} @endif
                            @if ($r->email) · {{ $r->email }} @endif
                        </div>
                    </div>
                    <div class="col-lg-4 small">
                        @if ($r->preferred_date)<div><i class="far fa-calendar me-1"></i><b>{{ $r->preferred_date->format('D, d M') }}</b> {{ $r->preferred_time }}</div>@endif
                        @if ($r->item)<div><i class="fas fa-vial me-1"></i>{{ $r->item }}</div>@endif
                        @if ($r->doctor)<div><i class="fas fa-user-md me-1"></i>{{ $r->doctor->name_en }}</div>@endif
                        @if ($r->branch)<div><i class="fas fa-code-branch me-1"></i>{{ $r->branch->name_en }}</div>@endif
                        @if ($r->organisation)<div><i class="fas fa-building me-1"></i>{{ $r->organisation }} @if ($r->staff_count)· {{ $r->staff_count }} staff @endif</div>@endif
                        @if ($r->address)<div><i class="fas fa-map-marker-alt me-1"></i>{{ $r->address }}</div>@endif
                        @if ($r->subject)<div><b>{{ $r->subject }}</b></div>@endif
                        @if ($r->message)<div class="text-body-secondary">{{ str($r->message)->limit(220) }}</div>@endif
                    </div>
                    <div class="col-lg-3">
                        @if (in_array('webrequest-handle', $permissions))
                            <form method="POST" action="{{ route('admin.web-requests.update', $r) }}" class="d-flex flex-column gap-1">
                                @csrf @method('PATCH')
                                <input class="form-control form-control-sm" name="staff_note" value="{{ $r->staff_note }}" placeholder="Note (e.g. confirmed for 10 am)" aria-label="Staff note" maxlength="255">
                                <div class="d-flex gap-1">
                                    <select class="form-select form-select-sm" name="status" aria-label="Status">
                                        @foreach (\App\Models\WebRequest::STATUSES as $k => $l)<option value="{{ $k }}" @selected($r->status === $k)>{{ $l }}</option>@endforeach
                                    </select>
                                    <button class="btn btn-sm btn-primary">Save</button>
                                </div>
                                @if ($r->handler)<div class="meta">{{ $r->handler->name }}, {{ $r->handled_at?->diffForHumans() }}</div>@endif
                            </form>
                        @else
                            <div class="small">{{ $r->staff_note }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="card"><div class="card-body text-center text-muted py-5"><i class="fas fa-inbox fa-2x mb-2 d-block"></i>Nothing here.</div></div>
    @endforelse

    <div class="mt-3">{{ $requests->links() }}</div>
@endsection
