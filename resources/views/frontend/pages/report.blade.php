@extends('frontend.layouts.app')

@section('title', __('অনলাইনে রিপোর্ট ডাউনলোড'))
@section('description', __('ইনভয়েস নম্বর ও রিপোর্ট পিন অথবা মোবাইল নম্বর দিয়ে লগইন করে আপনার ডায়াগনস্টিক রিপোর্ট নিরাপদে দেখুন ও ডাউনলোড করুন।'))

@section('content')
    @include('frontend.partials.page-header', [
        'crumb' => __('রিপোর্ট ডাউনলোড'),
        'eyebrow' => __('অনলাইন রিপোর্ট'),
        'title' => __('আপনার রিপোর্ট, <span class="grad">এক ক্লিকে</span>'),
        'lead' => __('রিপোর্ট প্রস্তুত হলে এসএমএস পাবেন। নিচের যেকোনো একটি পদ্ধতিতে রিপোর্ট খুলুন।'),
    ])

    <section class="section pt-0" aria-label="{{ __('রিপোর্ট ডাউনলোড ফর্ম') }}">
        <div class="container">
            <div class="row g-4 g-lg-5 align-items-start">
                <div class="col-lg-7 reveal">
                    <div class="form-card">
                        <div class="segmented segmented-tabs" role="tablist" aria-label="{{ __('রিপোর্ট খোলার পদ্ধতি') }}">
                            <button class="active" id="tab-invoice" type="button" role="tab" data-bs-toggle="pill" data-bs-target="#pane-invoice" aria-controls="pane-invoice" aria-selected="true">{{ __('ইনভয়েস ও পিন') }}</button>
                            <button id="tab-otp" type="button" role="tab" data-bs-toggle="pill" data-bs-target="#pane-otp" aria-controls="pane-otp" aria-selected="false">{{ __('মোবাইল ও পাসওয়ার্ড') }}</button>
                        </div>

                        <div class="tab-content mt-4">
                            <div class="tab-pane fade" id="pane-otp" role="tabpanel" aria-labelledby="tab-otp" tabindex="0">
                                <p>{{ __('মোবাইল নম্বর দিয়ে আপনার অ্যাকাউন্টে লগইন করুন। সেখানে আপনার ও পরিবারের সব রিপোর্ট পাবেন। প্রথমবার পাসওয়ার্ড হিসেবে আপনার মোবাইল নম্বরটিই দিন।') }}</p>
                                <a class="btn btn-primary btn-lg w-100" href="{{ route('login') }}"><i class="bi bi-box-arrow-in-right" aria-hidden="true"></i> {{ __('লগইন করুন') }}</a>
                            </div>

                            <div class="tab-pane fade show active" id="pane-invoice" role="tabpanel" aria-labelledby="tab-invoice" tabindex="0">
                                <form method="POST" action="{{ route('report.lookup') }}" novalidate>
                                    @csrf
                                    @error('invoice')<div class="note mt-0 mb-3 text-danger"><i class="bi bi-exclamation-circle" aria-hidden="true"></i> {{ $message }}</div>@enderror
                                    <div class="row g-3">
                                        <div class="col-sm-6">
                                            <label class="form-label" for="rpInvoice">{{ __('ইনভয়েস নম্বর') }}</label>
                                            <input class="form-control" id="rpInvoice" name="invoice" type="text" value="{{ old('invoice', request('invoice')) }}" placeholder="INV-MAIN-2611-00001" autocomplete="off" required>
                                        </div>
                                        <div class="col-sm-6">
                                            <label class="form-label" for="rpPin">{{ __('রিপোর্ট পিন') }}</label>
                                            <input class="form-control" id="rpPin" name="pin" type="text" inputmode="numeric" maxlength="8" placeholder="{{ __('ইনভয়েসে দেওয়া পিন') }}" autocomplete="off" required>
                                        </div>
                                    </div>
                                    <button class="btn btn-primary btn-lg w-100 mt-4" type="submit"><i class="bi bi-file-earmark-arrow-down" aria-hidden="true"></i> {{ __('রিপোর্ট দেখুন') }}</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <aside class="col-lg-5 reveal" style="--d:.1s" aria-label="{{ __('রিপোর্ট সম্পর্কে') }}">
                    <div class="aside-sticky">
                        <div class="card-soft aside-card">
                            <span class="icon-bubble"><i class="bi bi-qr-code-scan" aria-hidden="true"></i></span>
                            <h2 class="h5">{{ __('QR কোডে যাচাই') }}</h2>
                            <p class="mb-0">{{ __('রিপোর্টের QR কোড স্ক্যান করে রিপোর্টটি আসল কি না যাচাই করতে পারবেন।') }}</p>
                        </div>
                        <div class="card-soft aside-card">
                            <span class="icon-bubble"><i class="bi bi-shield-lock" aria-hidden="true"></i></span>
                            <h2 class="h5">{{ __('আপনার তথ্য সুরক্ষিত') }}</h2>
                            <p class="mb-0">{{ __('শুধুমাত্র আপনি বা আপনার অনুমোদিত ব্যক্তি রিপোর্ট খুলতে পারবেন।') }}</p>
                        </div>
                        <div class="card-soft aside-card">
                            <span class="icon-bubble"><i class="bi bi-question-circle" aria-hidden="true"></i></span>
                            <h2 class="h5">{{ __('রিপোর্ট খুঁজে পাচ্ছেন না?') }}</h2>
                            <p>{{ __('হটলাইনে ইনভয়েস নম্বর জানিয়ে সহায়তা নিন।') }}</p>
                            <a class="btn btn-ghost w-100" href="{{ $site['phone_link'] }}">{{ $site['phone'] }}</a>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>
@endsection
