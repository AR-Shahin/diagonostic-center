@extends('frontend.layouts.app')

@section('title', 'যোগাযোগ')
@section('description', $site['name'].'-এর হটলাইন, ঠিকানা, খোলার সময় ও যোগাযোগের ফর্ম। যেকোনো প্রশ্নে আমাদের সঙ্গে যোগাযোগ করুন।')

@section('content')
    @include('frontend.partials.page-header', [
        'crumb' => 'যোগাযোগ',
        'eyebrow' => 'যোগাযোগ',
        'title' => 'আমরা আছি <span class="grad">আপনার পাশে</span>',
        'lead' => 'কল করুন, মেসেজ পাঠান বা সরাসরি শাখায় আসুন।',
    ])

    <section class="section pt-0" aria-label="যোগাযোগের তথ্য">
        <div class="container">
            <div class="row g-4 mb-5">
                @foreach ([
                    ['bi-telephone-fill', 'হটলাইন / জরুরি', $site['phone'], $site['phone_link']],
                    ['bi-envelope-fill', 'ইমেইল', $site['email'], 'mailto:'.$site['email']],
                    ['bi-geo-alt-fill', 'ঠিকানা', $site['address'], null],
                    ['bi-clock-fill', 'খোলার সময়', $site['hours'], null],
                ] as [$icon, $label, $value, $href])
                    <div class="col-sm-6 col-lg-3 reveal" style="--d:{{ $loop->index * 0.06 }}s">
                        <div class="card-soft contact-card">
                            <span class="icon-bubble"><i class="bi {{ $icon }}" aria-hidden="true"></i></span>
                            <small>{{ $label }}</small>
                            @if ($href)
                                <a href="{{ $href }}"><strong>{{ $value }}</strong></a>
                            @else
                                <strong>{{ $value }}</strong>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="row g-4 align-items-stretch">
                <div class="col-lg-7 reveal">
                    <form class="form-card h-100" data-demo-form>
                        <h2 class="h3">মেসেজ পাঠান</h2>
                        <p class="text-body-secondary">আমরা যত দ্রুত সম্ভব উত্তর দেব।</p>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label" for="cName">আপনার নাম</label>
                                <input class="form-control" id="cName" name="name" type="text" autocomplete="name" required>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label" for="cPhone">মোবাইল নম্বর</label>
                                <input class="form-control" id="cPhone" name="phone" type="tel" inputmode="tel" autocomplete="tel" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="cSubject">বিষয়</label>
                                <input class="form-control" id="cSubject" name="subject" type="text">
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="cMessage">বার্তা</label>
                                <textarea class="form-control" id="cMessage" name="message" rows="5" required></textarea>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary btn-lg" type="submit">বার্তা পাঠান</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="col-lg-5 reveal" style="--d:.1s">
                    <div class="map-ph h-100">
                        <i class="bi bi-geo-alt-fill" aria-hidden="true"></i>
                        <p>ম্যাপ এখানে যুক্ত হবে</p>
                        <a class="btn btn-light" href="https://www.google.com/maps/search/?api=1&query={{ urlencode($site['address']) }}" target="_blank" rel="noopener"><i class="bi bi-sign-turn-right" aria-hidden="true"></i> গুগল ম্যাপে দেখুন</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('frontend.home.branches')
@endsection
