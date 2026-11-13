@extends('frontend.layouts.app')

@section('title', __('আমার অ্যাকাউন্ট – লগইন'))
@section('description', __('মোবাইল নম্বর দিয়ে লগইন করে আপনার ও পরিবারের বিল, পেমেন্ট ও রিপোর্ট দেখুন।'))
@section('robots', 'noindex, nofollow')

@section('content')
    @include('frontend.partials.page-header', [
        'crumb' => __('লগইন'),
        'eyebrow' => __('আমার অ্যাকাউন্ট'),
        'title' => __('আপনার <span class="grad">স্বাস্থ্য তথ্য</span>, এক জায়গায়'),
        'lead' => __('মোবাইল নম্বর দিয়ে লগইন করুন। প্রথমবার পাসওয়ার্ড হিসেবে আপনার মোবাইল নম্বরটিই দিন।'),
    ])

    <section class="section pt-0" aria-label="{{ __('লগইন ফর্ম') }}">
        <div class="container">
            <div class="row g-4 g-lg-5 align-items-start">
                <div class="col-lg-6 reveal">
                    <form class="form-card" method="POST" action="{{ route('portal.login') }}" novalidate>
                        @csrf
                        <h2 class="h4 mb-4">{{ __('লগইন করুন') }}</h2>

                        <label class="form-label" for="phone">{{ __('মোবাইল নম্বর') }}</label>
                        <input class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" type="tel" inputmode="tel" autocomplete="username" placeholder="01XXXXXXXXX" value="{{ old('phone') }}" required autofocus>
                        @error('phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror

                        <label class="form-label mt-3" for="password">{{ __('পাসওয়ার্ড') }}</label>
                        <div class="input-action">
                            <input class="form-control" id="password" name="password" type="password" autocomplete="current-password" required>
                            <button class="btn btn-ghost" type="button" data-toggle-password="password" aria-label="{{ __('পাসওয়ার্ড দেখুন') }}"><i class="bi bi-eye" aria-hidden="true"></i></button>
                        </div>

                        <div class="form-check mt-3">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                            <label class="form-check-label" for="remember">{{ __('এই ডিভাইসে লগইন রাখুন') }}</label>
                        </div>

                        <button class="btn btn-primary btn-lg w-100 mt-4" type="submit"><i class="bi bi-box-arrow-in-right" aria-hidden="true"></i> {{ __('লগইন') }}</button>
                        <p class="small text-body-secondary mt-3 mb-0">{{ __('পাসওয়ার্ড ভুলে গেলে হটলাইনে কল করুন বা কাউন্টারে বলুন—আমরা পাসওয়ার্ড আবার আপনার মোবাইল নম্বরে সেট করে দেব।') }}
                            <a href="{{ $site['phone_link'] }}">{{ $site['phone'] }}</a></p>
                    </form>
                </div>
                <aside class="col-lg-6 reveal" style="--d:.1s" aria-label="{{ __('অ্যাকাউন্ট সম্পর্কে') }}">
                    <div class="aside-sticky">
                        <div class="card-soft aside-card">
                            <span class="icon-bubble"><i class="bi bi-person-badge" aria-hidden="true"></i></span>
                            <h2 class="h5">{{ __('আলাদা রেজিস্ট্রেশন লাগে না') }}</h2>
                            <p class="mb-0">{{ __('প্রথমবার টেস্ট করার সময়ই আপনার মোবাইল নম্বরে অ্যাকাউন্ট তৈরি হয়ে যায়। পরের প্রতিটি টেস্ট একই অ্যাকাউন্টে যোগ হয়।') }}</p>
                        </div>
                        <div class="card-soft aside-card">
                            <span class="icon-bubble"><i class="bi bi-people" aria-hidden="true"></i></span>
                            <h2 class="h5">{{ __('পরিবারের সবার তথ্য এক জায়গায়') }}</h2>
                            <p class="mb-0">{{ __('একই নম্বরে পরিবারের অন্য সদস্যদের টেস্ট করালে সবার বিল ও রিপোর্ট এই অ্যাকাউন্টেই দেখবেন।') }}</p>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>
@endsection
