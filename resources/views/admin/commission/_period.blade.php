<form method="GET" class="d-flex flex-wrap gap-2 align-items-end">
    @foreach (request()->except(['from', 'to', 'print']) as $k => $v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach
    <div><label class="form-label small mb-1" for="pFrom">From</label><input type="date" class="form-control form-control-sm" id="pFrom" name="from" value="{{ $from->toDateString() }}"></div>
    <div><label class="form-label small mb-1" for="pTo">To</label><input type="date" class="form-control form-control-sm" id="pTo" name="to" value="{{ $to->toDateString() }}"></div>
    <button class="btn btn-sm btn-primary">Apply</button>
    <a class="btn btn-sm btn-light" href="{{ request()->fullUrlWithQuery(['from' => now()->startOfMonth()->toDateString(), 'to' => now()->toDateString()]) }}">This month</a>
    <a class="btn btn-sm btn-light" href="{{ request()->fullUrlWithQuery(['from' => now()->subMonthNoOverflow()->startOfMonth()->toDateString(), 'to' => now()->subMonthNoOverflow()->endOfMonth()->toDateString()]) }}">Last month</a>
</form>
