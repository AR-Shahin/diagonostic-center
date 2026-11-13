@extends('frontend.layouts.app')

@section('title', __('আমাদের সম্পর্কে'))
@section('description', __(':name সম্পর্কে জানুন: আমাদের লক্ষ্য, মান নিয়ন্ত্রণ, লাইসেন্স ও রোগীসেবার প্রতিশ্রুতি।', ['name' => $site['name']]))

@section('content')
    @include('frontend.partials.page-header', [
        'crumb' => __('আমাদের সম্পর্কে'),
        'eyebrow' => __('আমাদের পরিচয়'),
        'title' => __('সঠিক রোগ নির্ণয়ের পথে, <span class="grad">আপনার বিশ্বস্ত সঙ্গী</span>'),
        'lead' => __('নির্ভুল পরীক্ষা, স্বচ্ছ প্রক্রিয়া ও রোগীর প্রতি যত্ন—এটাই আমাদের কাজের ভিত্তি।'),
    ])

    <section class="section pt-0" aria-labelledby="story-title">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6 reveal">
                    <span class="eyebrow">{{ __('আমাদের গল্প') }}</span>
                    <h2 class="section-title text-start" id="story-title">{{ __('আমরা কারা') }}</h2>
                    <p class="body-lg">{{ __('[এখানে প্রতিষ্ঠানের সংক্ষিপ্ত ইতিহাস লিখুন: কবে, কেন এবং কীভাবে যাত্রা শুরু।]') }}</p>
                    <p>{{ __('[প্রতিষ্ঠানের লক্ষ্য, মূল্যবোধ ও রোগীসেবার দর্শন এখানে লিখুন। শুধুমাত্র যাচাইযোগ্য তথ্য ব্যবহার করুন।]') }}</p>
                    <div class="d-flex flex-wrap gap-3 mt-4">
                        <a class="btn btn-primary" href="{{ route('site.appointment') }}">{{ __('অ্যাপয়েন্টমেন্ট নিন') }}</a>
                        <a class="btn btn-ghost" href="{{ route('site.contact') }}">{{ __('যোগাযোগ করুন') }}</a>
                    </div>
                </div>
                <div class="col-lg-6 reveal" style="--d:.1s">
                    <div class="about-visual" aria-hidden="true">
                        <span class="av av-1"><i class="bi bi-eyedropper"></i></span>
                        <span class="av av-2"><i class="bi bi-heart-pulse"></i></span>
                        <span class="av av-3"><i class="bi bi-soundwave"></i></span>
                        <span class="av av-4"><i class="bi bi-patch-check-fill"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section section-alt" aria-labelledby="values-title">
        <div class="container">
            @include('frontend.partials.section-head', [
                'id' => 'values-title',
                'eyebrow' => __('আমাদের প্রতিশ্রুতি'),
                'title' => __('যা আমরা <span class="grad">মেনে চলি</span>'),
            ])
            <div class="row g-4">
                @foreach ([
                    ['bi-bullseye', __('আমাদের লক্ষ্য'), __('[প্রতিষ্ঠানের লক্ষ্য এখানে লিখুন।]')],
                    ['bi-eye', __('আমাদের দৃষ্টিভঙ্গি'), __('[ভবিষ্যৎ পরিকল্পনা ও দৃষ্টিভঙ্গি এখানে লিখুন।]')],
                    ['bi-heart', __('রোগীর প্রতি যত্ন'), __('[রোগীসেবার নীতি ও মূল্যবোধ এখানে লিখুন।]')],
                ] as [$icon, $title, $text])
                    <div class="col-md-4 reveal" style="--d:{{ $loop->index * 0.07 }}s">
                        <div class="why-card">
                            <span class="icon-bubble"><i class="bi {{ $icon }}" aria-hidden="true"></i></span>
                            <h3>{{ $title }}</h3>
                            <p>{{ $text }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section" aria-labelledby="license-title">
        <div class="container">
            @include('frontend.partials.section-head', [
                'id' => 'license-title',
                'eyebrow' => __('লাইসেন্স ও স্বীকৃতি'),
                'title' => __('আমাদের <span class="grad">নিবন্ধন ও সনদ</span>'),
                'sub' => __('শুধুমাত্র প্রকৃত ও যাচাইযোগ্য লাইসেন্স ও সনদ এখানে প্রদর্শন করুন।'),
            ])
            <div class="row g-4">
                @foreach ([__('স্বাস্থ্য অধিদপ্তরের নিবন্ধন নম্বর'), __('ট্রেড লাইসেন্স নম্বর'), __('অন্যান্য স্বীকৃতি / সনদ')] as $label)
                    <div class="col-md-4 reveal" style="--d:{{ $loop->index * 0.07 }}s">
                        <div class="card-soft license-card">
                            <span class="icon-bubble"><i class="bi bi-patch-check" aria-hidden="true"></i></span>
                            <h3>{{ $label }}</h3>
                            <p class="mb-0">{{ __('[নম্বর / বিবরণ]') }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    @include('frontend.home.why-us')
    @include('frontend.home.final-cta')
@endsection
