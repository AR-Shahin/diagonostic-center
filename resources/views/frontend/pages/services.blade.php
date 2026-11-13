@extends('frontend.layouts.app')

@section('title', __('আমাদের ডায়াগনস্টিক সেবা'))
@section('description', __('রক্ত পরীক্ষা, এক্স-রে, ইসিজি/ইকো, সিটি স্ক্যান, এমআরআই, আল্ট্রাসাউন্ড ও ডিজিটাল ইমেজিং—আমাদের সব ডায়াগনস্টিক সেবার বিস্তারিত।'))

@section('content')
    @include('frontend.partials.page-header', [
        'crumb' => __('আমাদের সেবা'),
        'eyebrow' => __('ডায়াগনস্টিক সেবা'),
        'title' => __('প্রয়োজনীয় সব সেবা, <span class="grad">এক জায়গায়</span>'),
        'lead' => __('প্রতিটি সেবা সম্পর্কে জানুন এবং পরীক্ষার আগে কী প্রস্তুতি নিতে হবে দেখে নিন।'),
    ])

    <section class="section pt-0" aria-label="{{ __('সেবার তালিকা') }}">
        <div class="container">
            <div class="service-list">
                @foreach ($content['services'] as $service)
                    <article class="service-row reveal" id="{{ $service['slug'] }}">
                        <span class="icon-bubble icon-lg"><i class="bi {{ $service['icon'] }}" aria-hidden="true"></i></span>
                        <div class="service-text">
                            <h2>{{ $service['bn'] }} <small lang="en" class="alt-lang">{{ $service['en'] }}</small></h2>
                            <p>{{ $service['desc'] }}</p>
                            <p class="service-prep"><i class="bi bi-info-circle" aria-hidden="true"></i> <b>{{ __('প্রস্তুতি:') }}</b> {{ $service['prep'] }}</p>
                        </div>
                        <a class="btn btn-primary" href="{{ route('site.appointment', ['service' => $service['slug']]) }}">{{ __('বুক করুন') }}</a>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    @include('frontend.home.final-cta')
@endsection
