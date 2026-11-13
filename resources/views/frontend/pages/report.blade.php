@extends('frontend.layouts.app')

@section('title', 'অনলাইনে রিপোর্ট ডাউনলোড')
@section('description', 'মোবাইল নম্বর ও ওটিপি অথবা ইনভয়েস নম্বর ও রিপোর্ট পিন দিয়ে আপনার ডায়াগনস্টিক রিপোর্ট নিরাপদে ডাউনলোড করুন।')

@section('content')
    @include('frontend.partials.page-header', [
        'crumb' => 'রিপোর্ট ডাউনলোড',
        'eyebrow' => 'অনলাইন রিপোর্ট',
        'title' => 'আপনার রিপোর্ট, <span class="grad">এক ক্লিকে</span>',
        'lead' => 'রিপোর্ট প্রস্তুত হলে এসএমএস পাবেন। নিচের যেকোনো একটি পদ্ধতিতে রিপোর্ট খুলুন।',
    ])

    <section class="section pt-0" aria-label="রিপোর্ট ডাউনলোড ফর্ম">
        <div class="container">
            <div class="row g-4 g-lg-5 align-items-start">
                <div class="col-lg-7 reveal">
                    <div class="form-card">
                        <div class="segmented segmented-tabs" role="tablist" aria-label="রিপোর্ট খোলার পদ্ধতি">
                            <button class="active" id="tab-otp" type="button" role="tab" data-bs-toggle="pill" data-bs-target="#pane-otp" aria-controls="pane-otp" aria-selected="true">মোবাইল ও ওটিপি</button>
                            <button id="tab-invoice" type="button" role="tab" data-bs-toggle="pill" data-bs-target="#pane-invoice" aria-controls="pane-invoice" aria-selected="false">ইনভয়েস ও পিন</button>
                        </div>

                        <div class="tab-content mt-4">
                            <div class="tab-pane fade show active" id="pane-otp" role="tabpanel" aria-labelledby="tab-otp" tabindex="0">
                                <form data-demo-form>
                                    <label class="form-label" for="rpPhone">নিবন্ধিত মোবাইল নম্বর</label>
                                    <div class="input-action">
                                        <input class="form-control" id="rpPhone" name="phone" type="tel" inputmode="tel" autocomplete="tel" placeholder="01XXXXXXXXX" required>
                                        <button class="btn btn-ghost" type="button" data-demo-action>ওটিপি পাঠান</button>
                                    </div>
                                    <label class="form-label mt-3" for="rpOtp">ওটিপি কোড</label>
                                    <input class="form-control otp-input" id="rpOtp" name="otp" type="text" inputmode="numeric" maxlength="6" autocomplete="one-time-code" placeholder="••••••" required>
                                    <button class="btn btn-primary btn-lg w-100 mt-4" type="submit"><i class="bi bi-file-earmark-arrow-down" aria-hidden="true"></i> রিপোর্ট দেখুন</button>
                                </form>
                            </div>

                            <div class="tab-pane fade" id="pane-invoice" role="tabpanel" aria-labelledby="tab-invoice" tabindex="0">
                                <form data-demo-form>
                                    <div class="row g-3">
                                        <div class="col-sm-6">
                                            <label class="form-label" for="rpInvoice">ইনভয়েস নম্বর</label>
                                            <input class="form-control" id="rpInvoice" name="invoice" type="text" placeholder="INV-000000" required>
                                        </div>
                                        <div class="col-sm-6">
                                            <label class="form-label" for="rpPin">রিপোর্ট পিন</label>
                                            <input class="form-control" id="rpPin" name="pin" type="text" placeholder="ইনভয়েসে দেওয়া পিন" required>
                                        </div>
                                    </div>
                                    <button class="btn btn-primary btn-lg w-100 mt-4" type="submit"><i class="bi bi-file-earmark-arrow-down" aria-hidden="true"></i> রিপোর্ট দেখুন</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <aside class="col-lg-5 reveal" style="--d:.1s" aria-label="রিপোর্ট সম্পর্কে">
                    <div class="aside-sticky">
                        <div class="card-soft aside-card">
                            <span class="icon-bubble"><i class="bi bi-qr-code-scan" aria-hidden="true"></i></span>
                            <h2 class="h5">QR কোডে যাচাই</h2>
                            <p class="mb-0">রিপোর্টের QR কোড স্ক্যান করে রিপোর্টটি আসল কি না যাচাই করতে পারবেন।</p>
                        </div>
                        <div class="card-soft aside-card">
                            <span class="icon-bubble"><i class="bi bi-shield-lock" aria-hidden="true"></i></span>
                            <h2 class="h5">আপনার তথ্য সুরক্ষিত</h2>
                            <p class="mb-0">শুধুমাত্র আপনি বা আপনার অনুমোদিত ব্যক্তি রিপোর্ট খুলতে পারবেন।</p>
                        </div>
                        <div class="card-soft aside-card">
                            <span class="icon-bubble"><i class="bi bi-question-circle" aria-hidden="true"></i></span>
                            <h2 class="h5">রিপোর্ট খুঁজে পাচ্ছেন না?</h2>
                            <p>হটলাইনে ইনভয়েস নম্বর জানিয়ে সহায়তা নিন।</p>
                            <a class="btn btn-ghost w-100" href="{{ $site['phone_link'] }}">{{ $site['phone'] }}</a>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>
@endsection
