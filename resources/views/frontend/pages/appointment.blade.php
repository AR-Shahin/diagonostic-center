@extends('frontend.layouts.app')

@section('title', __('অ্যাপয়েন্টমেন্ট নিন'))
@section('description', __('টেস্ট, চিকিৎসক বা বাসায় নমুনা সংগ্রহের জন্য অনলাইনে অ্যাপয়েন্টমেন্ট বুক করুন। পছন্দের তারিখ ও সময় বেছে নিন।'))

@section('content')
    @include('frontend.partials.page-header', [
        'crumb' => __('অ্যাপয়েন্টমেন্ট'),
        'eyebrow' => __('অনলাইন বুকিং'),
        'title' => __('এক মিনিটেই <span class="grad">অ্যাপয়েন্টমেন্ট নিন</span>'),
        'lead' => __('প্রয়োজনীয় তথ্য দিন, আমরা আপনার বুকিং নিশ্চিত করব।'),
    ])

    @php
        $slots = [__('সকাল ৮:০০'), __('সকাল ৯:০০'), __('সকাল ১০:০০'), __('সকাল ১১:০০'), __('বিকাল ৩:০০'), __('বিকাল ৪:০০'), __('সন্ধ্যা ৬:০০'), __('রাত ৭:০০')];
        $type = request('type') === 'home' ? 'home' : (request()->has('doctor') ? 'doctor' : 'test');
    @endphp

    <section class="section pt-0" aria-label="{{ __('অ্যাপয়েন্টমেন্ট ফর্ম') }}">
        <div class="container">
            <div class="row g-4 g-lg-5">
                <div class="col-lg-8 reveal">
                    <form class="form-card" data-demo-form>
                        <fieldset class="mb-4">
                            <legend class="form-label">{{ __('কী ধরনের সেবা?') }}</legend>
                            <div class="segmented" role="radiogroup">
                                @foreach (['test' => __('টেস্ট'), 'doctor' => __('চিকিৎসক'), 'home' => __('বাসায় নমুনা')] as $value => $label)
                                    <input class="btn-check" type="radio" name="type" id="type-{{ $value }}" value="{{ $value }}" @checked($type === $value)>
                                    <label for="type-{{ $value }}">{{ $label }}</label>
                                @endforeach
                            </div>
                        </fieldset>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="apTest">{{ __('টেস্ট / প্যাকেজ') }}</label>
                                <select class="form-select" id="apTest" name="test">
                                    <option value="">{{ __('বেছে নিন') }}</option>
                                    <optgroup label="{{ __('টেস্ট') }}">
                                        @foreach ($content['tests'] as $t)
                                            <option value="{{ $t['slug'] }}" @selected($selectedTest === $t['slug'])>{{ $t['name'] }} – {{ $t['bn'] }}</option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="{{ __('প্যাকেজ') }}">
                                        @foreach ($content['packages'] as $p)
                                            <option value="{{ $p['slug'] }}" @selected(request('package') === $p['slug'])>{{ $p['bn'] }}</option>
                                        @endforeach
                                    </optgroup>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="apDoctor">{{ __('চিকিৎসক (ঐচ্ছিক)') }}</label>
                                <select class="form-select" id="apDoctor" name="doctor">
                                    <option value="">{{ __('যেকোনো') }}</option>
                                    @foreach ($content['specialities'] as $spec)
                                        <option value="{{ $spec }}">{{ __(':s বিশেষজ্ঞ', ['s' => $spec]) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="apDate">{{ __('তারিখ') }}</label>
                                <input class="form-control" id="apDate" name="date" type="date" min="{{ now()->toDateString() }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="apBranch">{{ __('শাখা') }}</label>
                                <select class="form-select" id="apBranch" name="branch">
                                    @foreach ($content['branches'] as $b)
                                        <option>{{ $b['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <fieldset class="mt-4">
                            <legend class="form-label">{{ __('পছন্দের সময়') }}</legend>
                            <div class="slot-grid">
                                @foreach ($slots as $slot)
                                    <input class="btn-check" type="radio" name="slot" id="slot-{{ $loop->index }}" value="{{ $slot }}" @checked($loop->first)>
                                    <label for="slot-{{ $loop->index }}">{{ $slot }}</label>
                                @endforeach
                            </div>
                        </fieldset>

                        <hr class="my-4">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="apName">{{ __('রোগীর নাম') }}</label>
                                <input class="form-control" id="apName" name="name" type="text" autocomplete="name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="apPhone">{{ __('মোবাইল নম্বর') }}</label>
                                <input class="form-control" id="apPhone" name="phone" type="tel" inputmode="tel" autocomplete="tel" placeholder="01XXXXXXXXX" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="apAge">{{ __('বয়স') }}</label>
                                <input class="form-control" id="apAge" name="age" type="number" min="0" max="120" inputmode="numeric">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label" for="apAddress">{{ __('ঠিকানা (বাসায় নমুনার জন্য)') }}</label>
                                <input class="form-control" id="apAddress" name="address" type="text" autocomplete="street-address">
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="apNote">{{ __('অতিরিক্ত তথ্য') }}</label>
                                <textarea class="form-control" id="apNote" name="note" rows="3"></textarea>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary btn-lg w-100" type="submit">{{ __('বুকিং নিশ্চিত করুন') }}</button>
                            </div>
                        </div>
                    </form>
                </div>

                <aside class="col-lg-4 reveal" style="--d:.1s" aria-label="{{ __('সহায়ক তথ্য') }}">
                    <div class="aside-sticky">
                        <div class="card-soft aside-card">
                            <h2 class="h5">{{ __('সঙ্গে আনুন') }}</h2>
                            <ul class="check-list">
                                <li><i class="bi bi-check2-circle" aria-hidden="true"></i> {{ __('চিকিৎসকের প্রেসক্রিপশন (থাকলে)') }}</li>
                                <li><i class="bi bi-check2-circle" aria-hidden="true"></i> {{ __('পূর্বের রিপোর্ট') }}</li>
                                <li><i class="bi bi-check2-circle" aria-hidden="true"></i> {{ __('পরিচয়পত্র') }}</li>
                            </ul>
                        </div>
                        <div class="card-soft aside-card aside-cta">
                            <i class="bi bi-telephone-fill" aria-hidden="true"></i>
                            <h2 class="h5">{{ __('কল করে বুক করুন') }}</h2>
                            <a class="btn btn-light w-100" href="{{ $site['phone_link'] }}">{{ $site['phone'] }}</a>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>
@endsection
