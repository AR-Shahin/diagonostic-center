@extends('admin.layouts.master')

@section('title', 'Commission rates')

@push('css')
    <style>.card:hover { transform: none; }</style>
@endpush

@section('master_content')
    <x-admin.page-header :title="$referrer ? 'Rates for '.$referrer->name : 'Default commission rates'" icon="fas fa-percent"
        subtitle="The most specific rate wins: a referrer's own rate beats the default; a test beats its department; a department beats “all tests”.">
        <a class="btn btn-sm btn-light" href="{{ route('admin.commission.index') }}"><i class="fas fa-arrow-left me-1"></i> Commission</a>
    </x-admin.page-header>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-body">
                    <label class="form-label fw-semibold" for="who">Whose rates?</label>
                    <select class="form-select" id="who" onchange="location.href='{{ route('admin.commission.rates') }}' + (this.value ? '?referrer=' + this.value : '')">
                        <option value="">Default (every referrer)</option>
                        @foreach ($referrers as $id => $name)<option value="{{ $id }}" @selected($referrer?->id === $id)>{{ $name }}</option>@endforeach
                    </select>
                </div>
            </div>
            @if (in_array('commission-rates', $permissions))
                <form class="card" method="POST" action="{{ route('admin.commission.rates.save') }}">
                    @csrf
                    <input type="hidden" name="referrer_id" value="{{ $referrer?->id }}">
                    <div class="card-header"><b>Add or change a rate</b></div>
                    <div class="card-body">
                        @if ($errors->any())<div class="alert alert-danger small py-2">{{ $errors->first() }}</div>@endif
                        <label class="form-label fw-semibold">Applies to</label>
                        <div class="btn-group w-100 mb-3" role="group">
                            @foreach (['all' => 'All tests', 'department' => 'Department', 'test' => 'One test'] as $k => $v)
                                <input type="radio" class="btn-check" name="scope" id="sc_{{ $k }}" value="{{ $k }}" @checked(old('scope', 'all') === $k)>
                                <label class="btn btn-outline-primary btn-sm" for="sc_{{ $k }}">{{ $v }}</label>
                            @endforeach
                        </div>
                        <div data-scope="department"><x-admin.select name="department_id" label="Department" :options="$departments" placeholder="Select" /></div>
                        <div data-scope="test"><x-admin.select name="lab_test_id" label="Test" :options="$tests" placeholder="Select" /></div>
                        <div class="row g-2">
                            <div class="col-6"><x-admin.select name="type" label="Type" :options="['percent' => '% of price', 'fixed' => 'Fixed ৳ per test']" default="percent" /></div>
                            <div class="col-6"><x-admin.input name="value" label="Value" type="number" step="0.01" min="0" required /></div>
                        </div>
                        <button class="btn btn-success w-100"><i class="fas fa-save me-1"></i> Save rate</button>
                        <div class="form-text">Saving the same scope again replaces the old value. New rates apply to bills paid from now on.</div>
                    </div>
                </form>
            @endif
        </div>

        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header"><b>{{ $referrer ? $referrer->name.'’s own rates' : 'Default rates' }}</b></div>
                <div class="card-body p-0">
                    <table class="table align-middle mb-0">
                        <thead class="small text-muted"><tr><th>Applies to</th><th>Rate</th><th></th></tr></thead>
                        <tbody>
                            @forelse ($rates as $rate)
                                <tr>
                                    <td>{{ $rate->scopeLabel() }}</td>
                                    <td><b>{{ $rate->label() }}</b></td>
                                    <td class="text-end">
                                        @if (in_array('commission-rates', $permissions))
                                            <form method="POST" action="{{ route('admin.commission.rates.delete', $rate) }}" onsubmit="return confirm('Remove this rate?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-4">{{ $referrer ? 'No own rates — the default rates below apply.' : 'No default rates yet. Without a rate, no commission is earned.' }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($referrer)
                <div class="card">
                    <div class="card-header"><b>Default rates (used when this referrer has no own rate)</b></div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            @forelse ($defaults as $rate)
                                <tr><td>{{ $rate->scopeLabel() }}</td><td>{{ $rate->label() }}</td></tr>
                            @empty
                                <tr><td class="text-muted p-3">None.</td></tr>
                            @endforelse
                        </table>
                    </div>
                </div>
            @endif
            <div class="alert alert-light border small mt-3 mb-0">
                <b>Example:</b> default Pathology 30%, Ultrasonography 15%; Dr. X has his own 20% for Ultrasonography.
                A CBC (Pathology, ৳400) earns ৳120 for everyone; a USG (৳1,500) earns ৳225 for others and ৳300 for Dr. X.
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        const showScope = () => {
            const v = document.querySelector('[name=scope]:checked')?.value;
            document.querySelectorAll('[data-scope]').forEach((el) => { el.hidden = el.dataset.scope !== v; });
        };
        document.querySelectorAll('[name=scope]').forEach((r) => r.addEventListener('change', showScope));
        showScope();
    </script>
@endpush
