@extends('frontend.layouts.app')

@section('title', 'আমাদের সম্পর্কে')
@section('description', $site['name'].' সম্পর্কে জানুন: আমাদের লক্ষ্য, মান নিয়ন্ত্রণ, লাইসেন্স ও রোগীসেবার প্রতিশ্রুতি।')

@section('content')
    @include('frontend.partials.page-header', [
        'crumb' => 'আমাদের সম্পর্কে',
        'eyebrow' => 'আমাদের পরিচয়',
        'title' => 'সঠিক রোগ নির্ণয়ের পথে, <span class="grad">আপনার বিশ্বস্ত সঙ্গী</span>',
        'lead' => 'নির্ভুল পরীক্ষা, স্বচ্ছ প্রক্রিয়া ও রোগীর প্রতি যত্ন—এটাই আমাদের কাজের ভিত্তি।',
    ])

    <section class="section pt-0" aria-labelledby="story-title">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6 reveal">
                    <span class="eyebrow">আমাদের গল্প</span>
                    <h2 class="section-title text-start" id="story-title">আমরা কারা</h2>
                    <p class="body-lg">[এখানে প্রতিষ্ঠানের সংক্ষিপ্ত ইতিহাস লিখুন: কবে, কেন এবং কীভাবে যাত্রা শুরু।]</p>
                    <p>[প্রতিষ্ঠানের লক্ষ্য, মূল্যবোধ ও রোগীসেবার দর্শন এখানে লিখুন। শুধুমাত্র যাচাইযোগ্য তথ্য ব্যবহার করুন।]</p>
                    <div class="d-flex flex-wrap gap-3 mt-4">
                        <a class="btn btn-primary" href="{{ route('site.appointment') }}">অ্যাপয়েন্টমেন্ট নিন</a>
                        <a class="btn btn-ghost" href="{{ route('site.contact') }}">যোগাযোগ করুন</a>
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
                'eyebrow' => 'আমাদের প্রতিশ্রুতি',
                'title' => 'যা আমরা <span class="grad">মেনে চলি</span>',
            ])
            <div class="row g-4">
                @foreach ([
                    ['bi-bullseye', 'আমাদের লক্ষ্য', '[প্রতিষ্ঠানের লক্ষ্য এখানে লিখুন।]'],
                    ['bi-eye', 'আমাদের দৃষ্টিভঙ্গি', '[ভবিষ্যৎ পরিকল্পনা ও দৃষ্টিভঙ্গি এখানে লিখুন।]'],
                    ['bi-heart', 'রোগীর প্রতি যত্ন', '[রোগীসেবার নীতি ও মূল্যবোধ এখানে লিখুন।]'],
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
                'eyebrow' => 'লাইসেন্স ও স্বীকৃতি',
                'title' => 'আমাদের <span class="grad">নিবন্ধন ও সনদ</span>',
                'sub' => 'শুধুমাত্র প্রকৃত ও যাচাইযোগ্য লাইসেন্স ও সনদ এখানে প্রদর্শন করুন।',
            ])
            <div class="row g-4">
                @foreach (['স্বাস্থ্য অধিদপ্তরের নিবন্ধন নম্বর', 'ট্রেড লাইসেন্স নম্বর', 'অন্যান্য স্বীকৃতি / সনদ'] as $label)
                    <div class="col-md-4 reveal" style="--d:{{ $loop->index * 0.07 }}s">
                        <div class="card-soft license-card">
                            <span class="icon-bubble"><i class="bi bi-patch-check" aria-hidden="true"></i></span>
                            <h3>{{ $label }}</h3>
                            <p class="mb-0">[নম্বর / বিবরণ]</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    @include('frontend.home.why-us')
    @include('frontend.home.final-cta')
@endsection
