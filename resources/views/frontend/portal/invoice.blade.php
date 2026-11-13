@extends('frontend.layouts.app')

@section('title', $invoice->invoice_no)
@section('robots', 'noindex, nofollow')

@section('content')
    <section class="page-hero" aria-labelledby="page-title">
        <div class="hero-bg" aria-hidden="true"></div>
        <div class="container">
            <nav aria-label="breadcrumb"><ol class="crumbs">
                <li><a href="{{ route('portal.dashboard') }}">{{ __('আমার অ্যাকাউন্ট') }}</a></li>
                <li aria-current="page">{{ $invoice->invoice_no }}</li>
            </ol></nav>
            <h1 class="page-title mb-1" id="page-title">{{ $invoice->patient_name }}</h1>
            <p class="page-lead">{{ $invoice->invoice_no }} · {{ $invoice->created_at->format('d M Y, h:i A') }} · {{ $invoice->branch?->localized('name') }}</p>
        </div>
    </section>

    <section class="section pt-0" aria-label="{{ __('বিলের বিবরণ') }}">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="form-card">
                        <h2 class="h5 mb-3">{{ __('টেস্টসমূহ') }}</h2>
                        <ul class="portal-items">
                            @foreach ($invoice->billedItems as $item)
                                <li>
                                    <div><b>{{ $item->name }}</b>
                                        @if ($item->children->isNotEmpty())<small>{{ $item->children->pluck('name')->implode(', ') }}</small>@endif
                                    </div>
                                    <span class="num">৳ {{ number_format((float) $item->price, 2) }}</span>
                                </li>
                            @endforeach
                        </ul>
                        @php
                            $readyCount = $invoice->reportableItems()->count();
                        @endphp
                        @if ($readyCount && $invoice->canReleaseReport(true))
                            <a class="btn btn-primary btn-lg w-100 mt-3" href="{{ route('portal.report', $invoice) }}"><i class="bi bi-file-earmark-medical" aria-hidden="true"></i> {{ __('রিপোর্ট দেখুন (:n টি টেস্ট)', ['n' => $readyCount]) }}</a>
                        @endif
                        <p class="note"><i class="bi bi-clock" aria-hidden="true"></i>
                            {{ __('রিপোর্ট প্রস্তুত হওয়ার সম্ভাব্য সময়: :t', ['t' => $invoice->delivery_at?->format('d M Y, h:i A')]) }}
                            @if (! $invoice->canReleaseReport(true) && $invoice->hasDue()) {{ __('বকেয়া পরিশোধের পর রিপোর্ট দেখা যাবে।') }} @endif
                        </p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="form-card">
                        <div class="portal-sum"><span>{{ __('মোট') }}</span><span>৳ {{ number_format((float) $invoice->subtotal, 2) }}</span></div>
                        @if ((float) $invoice->discount_amount > 0)<div class="portal-sum"><span>{{ __('ছাড়') }}</span><span>− ৳ {{ number_format((float) $invoice->discount_amount, 2) }}</span></div>@endif
                        @if ((float) $invoice->vat_amount > 0)<div class="portal-sum"><span>VAT</span><span>৳ {{ number_format((float) $invoice->vat_amount, 2) }}</span></div>@endif
                        @if ((float) $invoice->home_collection_charge > 0)<div class="portal-sum"><span>{{ __('বাসায় নমুনা সংগ্রহ') }}</span><span>৳ {{ number_format((float) $invoice->home_collection_charge, 2) }}</span></div>@endif
                        <div class="portal-sum is-total"><span>{{ __('প্রদেয়') }}</span><span>৳ {{ number_format((float) $invoice->net_amount, 2) }}</span></div>
                        <div class="portal-sum"><span>{{ __('পরিশোধিত') }}</span><span>৳ {{ number_format((float) $invoice->paid_amount, 2) }}</span></div>
                        <div class="portal-sum {{ $invoice->hasDue() ? 'is-due' : '' }}"><span>{{ __('বকেয়া') }}</span><span>৳ {{ number_format((float) $invoice->due_amount, 2) }}</span></div>
                        @if ($invoice->isCancelled())<p class="note">{{ __('এই বিলটি বাতিল করা হয়েছে।') }}</p>@endif
                        <button type="button" class="btn btn-ghost w-100 mt-3" onclick="window.print()"><i class="bi bi-printer" aria-hidden="true"></i> {{ __('প্রিন্ট') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
