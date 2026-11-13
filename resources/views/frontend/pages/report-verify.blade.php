@extends('frontend.layouts.app')

@section('title', __('রিপোর্ট যাচাই'))
@section('robots', 'noindex, nofollow')

@section('content')
    @php
        $mask = fn (string $name) => collect(preg_split('/\s+/u', trim($name)))->map(fn ($w) => mb_substr($w, 0, 1).str_repeat('•', max(1, mb_strlen($w) - 1)))->implode(' ');
        $verified = $invoice?->labItems->filter(fn ($i) => $i->result?->isVerified()) ?? collect();
    @endphp
    @include('frontend.partials.page-header', [
        'crumb' => __('রিপোর্ট যাচাই'),
        'eyebrow' => __('QR যাচাই'),
        'title' => $invoice && $verified->isNotEmpty() && ! $invoice->isCancelled() ? __('এই রিপোর্টটি <span class="grad">আসল</span>') : __('রিপোর্ট <span class="grad">পাওয়া যায়নি</span>'),
        'lead' => $invoice && $verified->isNotEmpty() && ! $invoice->isCancelled()
            ? __(':name থেকে ইস্যু ও যাচাই করা হয়েছে। নিচের তথ্য আপনার হাতের রিপোর্টের সঙ্গে মিলিয়ে নিন।', ['name' => $site['name']])
            : __('এই QR কোডের সঙ্গে মেলে এমন কোনো যাচাইকৃত রিপোর্ট নেই। রিপোর্টটি সন্দেহজনক হলে আমাদের হটলাইনে জানান।'),
    ])

    @if ($invoice && $verified->isNotEmpty() && ! $invoice->isCancelled())
        <section class="section pt-0" aria-label="{{ __('যাচাইয়ের তথ্য') }}">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="form-card">
                            <div class="portal-sum"><span>{{ __('রোগী') }}</span><b>{{ $mask($invoice->patient_name) }}</b></div>
                            <div class="portal-sum"><span>{{ __('ইনভয়েস') }}</span><span>{{ $invoice->invoice_no }}</span></div>
                            <div class="portal-sum"><span>{{ __('তারিখ') }}</span><span>{{ $invoice->created_at->format('d M Y') }}</span></div>
                            <div class="portal-sum"><span>{{ __('শাখা') }}</span><span>{{ $invoice->branch?->localized('name') }}</span></div>
                            <h2 class="h6 mt-4 mb-2">{{ __('যাচাইকৃত টেস্ট') }}</h2>
                            <ul class="portal-items">
                                @foreach ($verified as $item)
                                    <li><div><b>{{ $item->name }}</b><small>{{ __('যাচাই করেছেন: :n', ['n' => $item->result->verifiedBy?->name]) }}</small></div>
                                        <span class="num">{{ $item->result->verified_at->format('d M Y, h:i A') }}</span></li>
                                @endforeach
                            </ul>
                            <p class="note"><i class="bi bi-shield-check" aria-hidden="true"></i> {{ __('গোপনীয়তার জন্য এখানে ফলাফল দেখানো হয় না।') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif
@endsection
