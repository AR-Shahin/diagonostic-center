@extends('frontend.layouts.app')

@section('title', __('পাসওয়ার্ড পরিবর্তন'))
@section('robots', 'noindex, nofollow')

@section('content')
    @include('frontend.partials.page-header', [
        'crumb' => __('পাসওয়ার্ড'),
        'eyebrow' => __('আমার অ্যাকাউন্ট'),
        'title' => __('নিজের <span class="grad">পাসওয়ার্ড</span> সেট করুন'),
        'lead' => __('আপনার রিপোর্ট ব্যক্তিগত তথ্য। মোবাইল নম্বর ছাড়া অন্য একটি পাসওয়ার্ড দিন, যা শুধু আপনি জানেন।'),
    ])

    <section class="section pt-0" aria-label="{{ __('পাসওয়ার্ড ফর্ম') }}">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6 reveal">
                    @if (session('portal_status'))
                        <div class="note mt-0 mb-3"><i class="bi bi-shield-lock" aria-hidden="true"></i> {{ session('portal_status') }}</div>
                    @endif
                    <form class="form-card" method="POST" action="{{ route('portal.password.update') }}" novalidate>
                        @csrf
                        @method('PUT')
                        <label class="form-label" for="current_password">{{ __('বর্তমান পাসওয়ার্ড') }}</label>
                        <input class="form-control @error('current_password') is-invalid @enderror" id="current_password" name="current_password" type="password" autocomplete="current-password" required>
                        @error('current_password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        @if (auth('web')->user()->must_change_password)
                            <div class="form-text">{{ __('প্রথমবার হলে বর্তমান পাসওয়ার্ড হলো আপনার মোবাইল নম্বর।') }}</div>
                        @endif

                        <label class="form-label mt-3" for="password">{{ __('নতুন পাসওয়ার্ড') }}</label>
                        <input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" autocomplete="new-password" minlength="6" required>
                        @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        <div class="form-text">{{ __('কমপক্ষে ৬ অক্ষর।') }}</div>

                        <label class="form-label mt-3" for="password_confirmation">{{ __('নতুন পাসওয়ার্ড আবার লিখুন') }}</label>
                        <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>

                        <button class="btn btn-primary btn-lg w-100 mt-4" type="submit">{{ __('পাসওয়ার্ড সংরক্ষণ করুন') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
