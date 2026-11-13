@extends('frontend.layouts.app')

@section('title', 'হেলথ চেকআপ প্যাকেজ')
@section('description', 'ফুল বডি চেকআপ, ডায়াবেটিস স্ক্রিনিং, হার্ট হেলথ, উইমেন্স হেলথ ও সিনিয়র সিটিজেন প্যাকেজ। কম খরচে একসাথে একাধিক টেস্ট।')

@section('content')
    @include('frontend.partials.page-header', [
        'crumb' => 'হেলথ প্যাকেজ',
        'eyebrow' => 'হেলথ চেকআপ প্যাকেজ',
        'title' => 'একসাথে অনেক টেস্ট, <span class="grad">সাশ্রয়ী মূল্যে</span>',
        'lead' => 'আপনার বয়স ও প্রয়োজন অনুযায়ী প্যাকেজ বেছে নিন। প্রতিটিতে কী কী টেস্ট আছে, সব দেখে নিন।',
    ])

    <section class="section pt-0" aria-label="প্যাকেজ তালিকা">
        <div class="container">
            <div class="row g-4">
                @foreach ($content['packages'] as $pkg)
                    <div class="col-md-6 col-lg-4 reveal" style="--d:{{ ($loop->index % 3) * 0.07 }}s">
                        @include('frontend.partials.package-card', ['pkg' => $pkg, 'limit' => 20])
                    </div>
                @endforeach
            </div>

            <p class="note reveal"><i class="bi bi-info-circle" aria-hidden="true"></i> প্যাকেজের টেস্ট তালিকা ও মূল্য পরিবর্তন হতে পারে। চূড়ান্ত তথ্যের জন্য বুকিংয়ের সময় নিশ্চিত করুন।</p>
        </div>
    </section>

    @include('frontend.home.final-cta')
@endsection
