@extends('frontend.layouts.app')

@section('title', __('আমার অ্যাকাউন্ট'))
@section('robots', 'noindex, nofollow')

@section('content')
    <section class="page-hero" aria-labelledby="page-title">
        <div class="hero-bg" aria-hidden="true"></div>
        <div class="container">
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-3">
                <div>
                    <span class="eyebrow">{{ __('আমার অ্যাকাউন্ট') }}</span>
                    <h1 class="page-title mb-1" id="page-title">{{ __('স্বাগতম, :name', ['name' => $user->name]) }}</h1>
                    <p class="page-lead">{{ $user->phone }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a class="btn btn-ghost" href="{{ route('portal.password.edit') }}"><i class="bi bi-key" aria-hidden="true"></i> {{ __('পাসওয়ার্ড') }}</a>
                    <form method="POST" action="{{ route('portal.logout') }}">@csrf<button class="btn btn-ghost" type="submit"><i class="bi bi-box-arrow-right" aria-hidden="true"></i> {{ __('লগআউট') }}</button></form>
                </div>
            </div>
        </div>
    </section>

    <section class="section pt-0" aria-label="{{ __('বিল ও রিপোর্ট') }}">
        <div class="container">
            @if (session('portal_status'))
                <div class="note mt-0 mb-4"><i class="bi bi-check2-circle" aria-hidden="true"></i> {{ session('portal_status') }}</div>
            @endif

            <div class="row g-3 mb-4">
                <div class="col-sm-4"><div class="card-soft portal-stat"><small>{{ __('প্রোফাইল') }}</small><b>{{ $patients->count() }}</b></div></div>
                <div class="col-sm-4"><div class="card-soft portal-stat"><small>{{ __('মোট বিল') }}</small><b>{{ $invoices->where('status', '!=', 'cancelled')->count() }}</b></div></div>
                <div class="col-sm-4"><div class="card-soft portal-stat {{ $due > 0 ? 'is-due' : '' }}"><small>{{ __('বকেয়া') }}</small><b>৳ {{ number_format($due) }}</b></div></div>
            </div>

            <h2 class="h5 mb-3">{{ __('পরিবারের সদস্য') }}</h2>
            <div class="chip-row mb-4" style="margin-top:0">
                @foreach ($patients as $p)
                    <span class="chip"><i class="bi bi-person" aria-hidden="true"></i> {{ $p->name }} · {{ $p->ageLabel() }} · {{ $p->patient_code }}</span>
                @endforeach
            </div>

            <h2 class="h5 mb-3">{{ __('আমার বিল') }}</h2>
            @forelse ($invoices as $inv)
                <a href="{{ route('portal.invoice', $inv) }}" class="portal-bill card-soft">
                    <div class="portal-bill-main">
                        <b>{{ $inv->invoice_no }}</b>
                        <span>{{ $inv->patient_name }} · {{ $inv->created_at->format('d M Y') }} · {{ $inv->branch?->localized('name') }}</span>
                        <small>{{ $inv->labItems->pluck('name')->take(4)->implode(', ') }}{{ $inv->labItems->count() > 4 ? ' …' : '' }}</small>
                    </div>
                    <div class="portal-bill-side">
                        <span class="price-now">৳ {{ number_format((float) $inv->net_amount) }}</span>
                        @if ($inv->isCancelled())
                            <span class="tag tag-light text-bg-secondary">{{ __('বাতিল') }}</span>
                        @elseif ($inv->hasDue())
                            <span class="tag portal-due">{{ __('বকেয়া ৳:n', ['n' => number_format((float) $inv->due_amount)]) }}</span>
                        @else
                            <span class="tag tag-teal">{{ __('পরিশোধিত') }}</span>
                        @endif
                        @if ($inv->ready_count && ! $inv->isCancelled())
                            <span class="tag tag-blue"><i class="bi bi-file-earmark-medical" aria-hidden="true"></i> {{ __('রিপোর্ট প্রস্তুত') }}</span>
                        @endif
                    </div>
                    <i class="bi bi-chevron-right" aria-hidden="true"></i>
                </a>
            @empty
                <div class="empty-state"><i class="bi bi-receipt" aria-hidden="true"></i><h2>{{ __('এখনো কোনো বিল নেই') }}</h2></div>
            @endforelse
        </div>
    </section>
@endsection
