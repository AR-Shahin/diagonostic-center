@extends('frontend.layouts.app')

@section('title', __('ডায়াগনস্টিক টেস্ট – মূল্য, প্রস্তুতি ও রিপোর্ট সময়'))
@section('description', __('রক্ত, প্রস্রাব, হরমোন, এক্স-রে, আল্ট্রাসাউন্ডসহ সব ডায়াগনস্টিক টেস্ট খুঁজুন। মূল্য, প্রস্তুতি ও রিপোর্ট ডেলিভারির সময় দেখে অনলাইনে বুক করুন।'))

@section('content')
    @include('frontend.partials.page-header', [
        'crumb' => __('ডায়াগনস্টিক টেস্ট'),
        'eyebrow' => __('টেস্ট ক্যাটালগ'),
        'title' => __('আপনার প্রয়োজনীয় <span class="grad">টেস্ট খুঁজুন</span>'),
        'lead' => __('নাম লিখে বা বিভাগ বেছে নিন। প্রতিটি টেস্টে মূল্য, প্রস্তুতি ও রিপোর্টের সময় দেওয়া আছে।'),
    ])

    <section class="section pt-0" aria-label="{{ __('টেস্ট তালিকা') }}">
        <div class="container" data-filter-scope>
            <div class="filter-bar reveal">
                <form class="search-pill" role="search" onsubmit="return false">
                    <label for="testSearch" class="visually-hidden">{{ __('টেস্টের নাম') }}</label>
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <input id="testSearch" type="search" placeholder="{{ __('টেস্টের নাম লিখুন') }}" value="{{ $q }}" data-filter-input autocomplete="off">
                </form>

                <div class="chip-scroll" role="group" aria-label="{{ __('বিভাগ অনুযায়ী ফিল্টার') }}">
                    <button type="button" class="chip-btn {{ $cat === '' ? 'is-active' : '' }}" data-filter-chip="" aria-pressed="{{ $cat === '' ? 'true' : 'false' }}">{{ __('সব') }}</button>
                    @foreach ($content['departments'] as $slug => $d)
                        <button type="button" class="chip-btn {{ $cat === $slug ? 'is-active' : '' }}" data-filter-chip="{{ $slug }}" aria-pressed="{{ $cat === $slug ? 'true' : 'false' }}">{{ $d['bn'] }}</button>
                    @endforeach
                </div>
            </div>

            <p class="result-count" role="status" aria-live="polite" data-filter-count data-template="{{ __(':n টি ফলাফল') }}"></p>

            <div class="row g-4">
                @foreach ($content['tests'] as $test)
                    <div class="col-md-6 col-lg-4" data-filter-wrap>
                        @include('frontend.partials.test-card', ['test' => $test, 'departments' => $content['departments']])
                    </div>
                @endforeach
            </div>

            <div class="empty-state" data-filter-empty hidden>
                <i class="bi bi-search" aria-hidden="true"></i>
                <h2>{{ __('কোনো টেস্ট পাওয়া যায়নি') }}</h2>
                <p>{{ __('অন্য নামে খুঁজুন, অথবা আমাদের হটলাইনে কল করুন।') }}</p>
                <a class="btn btn-primary" href="{{ $site['phone_link'] }}">{{ $site['phone'] }}</a>
            </div>
        </div>
    </section>

    @include('frontend.home.final-cta')
@endsection
