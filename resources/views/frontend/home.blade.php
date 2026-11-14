@extends('frontend.layouts.app')

@section('description', __('টেস্ট খুঁজুন, অ্যাপয়েন্টমেন্ট নিন, বাসা থেকে নমুনা দিন এবং অনলাইনে রিপোর্ট ডাউনলোড করুন। প্যাথলজি, ইমেজিং, কার্ডিওলজি ও হেলথ প্যাকেজ—সবই এক জায়গায়।'))

@section('content')
    @include('frontend.home.hero')
    @include('frontend.home.stats')
    @include('frontend.home.quick-access')
    @include('frontend.home.popular-tests')
    @include('frontend.home.packages')
    @include('frontend.home.departments')
    @include('frontend.home.services')
    @if ($content['doctors'])
        @include('frontend.home.doctors')
    @endif
    @if ($content['home_collection']['enabled'])
        @include('frontend.home.home-collection')
    @endif
    @include('frontend.home.why-us')
    @include('frontend.home.how-it-works')
    @include('frontend.home.branches')
    @if ($content['testimonials'])
        @include('frontend.home.testimonials')
    @endif
    @include('frontend.home.corporate')
    @if ($content['articles'])
        @include('frontend.home.articles')
    @endif
    @if ($content['faqs'])
        @include('frontend.home.faq')
    @endif
    @include('frontend.home.final-cta')
@endsection
