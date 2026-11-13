@extends('frontend.layouts.app')

@section('title', __('আমাদের চিকিৎসক'))
@section('description', __('বিশেষজ্ঞ চিকিৎসকদের প্রোফাইল, বিশেষত্ব, চেম্বারের সময় ও ভিজিট ফি দেখুন এবং অনলাইনে অ্যাপয়েন্টমেন্ট বুক করুন।'))

@section('content')
    @include('frontend.partials.page-header', [
        'crumb' => __('আমাদের ডাক্তার'),
        'eyebrow' => __('বিশেষজ্ঞ চিকিৎসক'),
        'title' => __('আপনার প্রয়োজনের <span class="grad">সঠিক চিকিৎসক</span>'),
        'lead' => __('বিশেষত্ব অনুযায়ী খুঁজুন, চেম্বারের সময় দেখে অ্যাপয়েন্টমেন্ট নিন।'),
    ])

    <section class="section pt-0" aria-label="{{ __('চিকিৎসক তালিকা') }}">
        <div class="container" data-filter-scope>
            <div class="filter-bar reveal">
                <form class="search-pill" role="search" onsubmit="return false">
                    <label for="doctorSearch" class="visually-hidden">{{ __('চিকিৎসকের নাম বা বিশেষত্ব') }}</label>
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <input id="doctorSearch" type="search" placeholder="{{ __('চিকিৎসকের নাম বা বিশেষত্ব') }}" data-filter-input autocomplete="off">
                </form>

                <div class="chip-scroll" role="group" aria-label="{{ __('বিশেষত্ব অনুযায়ী ফিল্টার') }}">
                    <button type="button" class="chip-btn is-active" data-filter-chip="" aria-pressed="true">{{ __('সব') }}</button>
                    @foreach ($content['specialities'] as $spec)
                        <button type="button" class="chip-btn" data-filter-chip="{{ $spec }}" aria-pressed="false">{{ $spec }}</button>
                    @endforeach
                </div>
            </div>

            <p class="result-count" role="status" aria-live="polite" data-filter-count data-template="{{ __(':n টি ফলাফল') }}"></p>

            <div class="row g-4">
                @foreach ($content['doctors'] as $doctor)
                    <div class="col-sm-6 col-lg-3" data-filter-wrap>
                        @include('frontend.partials.doctor-card', ['doctor' => $doctor])
                    </div>
                @endforeach
            </div>

            <div class="empty-state" data-filter-empty hidden>
                <i class="bi bi-person-x" aria-hidden="true"></i>
                <h2>{{ __('কোনো চিকিৎসক পাওয়া যায়নি') }}</h2>
                <p>{{ __('অন্য বিশেষত্ব বেছে নিন, অথবা হটলাইনে কল করুন।') }}</p>
            </div>
        </div>
    </section>

    @include('frontend.home.final-cta')
@endsection
