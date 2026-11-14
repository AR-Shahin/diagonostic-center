@extends('frontend.layouts.app')

@section('title', __('স্বাস্থ্য পরামর্শ'))
@section('description', __('স্বাস্থ্য সচেতনতা, টেস্টের প্রস্তুতি ও মৌসুমি পরামর্শ — আমাদের চিকিৎসক ও ল্যাব টিমের লেখা।'))

@section('content')
    @include('frontend.partials.page-header', [
        'crumb' => __('স্বাস্থ্য পরামর্শ'),
        'eyebrow' => __('স্বাস্থ্য পরামর্শ ও নিবন্ধ'),
        'title' => __('সুস্থ থাকতে <span class="grad">জানুন আরও</span>'),
        'lead' => __('স্বাস্থ্য সচেতনতা, টেস্টের প্রস্তুতি ও মৌসুমি পরামর্শ।'),
    ])

    <section class="section pt-0" aria-label="{{ __('নিবন্ধের তালিকা') }}">
        <div class="container">
            @if ($articles)
                <div class="row g-4">
                    @foreach ($articles as $article)
                        <div class="col-md-6 col-lg-4 reveal" style="--d:{{ ($loop->index % 3) * 0.07 }}s">
                            @include('frontend.partials.article-card', ['article' => $article])
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <i class="bi bi-journal-medical" aria-hidden="true"></i>
                    <h2>{{ __('শীঘ্রই নতুন লেখা আসছে') }}</h2>
                    <p>{{ __('ততক্ষণ কোনো প্রশ্ন থাকলে হটলাইনে কল করুন।') }} <a href="{{ $site['phone_link'] }}">{{ $site['phone'] }}</a></p>
                </div>
            @endif
        </div>
    </section>

    @include('frontend.home.final-cta')
@endsection
