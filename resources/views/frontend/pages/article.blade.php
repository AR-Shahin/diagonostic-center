@extends('frontend.layouts.app')

@section('title', $article['title'])
@section('description', $article['excerpt'] ?: str(strip_tags($article['body']))->squish()->limit(155))

@push('head')
    <meta property="article:published_time" content="{{ $article['date']->toAtomString() }}">
    <meta property="article:modified_time" content="{{ $article['updated']->toAtomString() }}">
    @if ($article['image'])<meta property="og:image" content="{{ $article['image'] }}">@endif
@endpush

@push('schema')
    <script type="application/ld+json">{!! json_encode(array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $article['title'],
        'description' => $article['excerpt'],
        'image' => $article['image'],
        'datePublished' => $article['date']->toAtomString(),
        'dateModified' => $article['updated']->toAtomString(),
        'mainEntityOfPage' => $article['url'],
        'publisher' => ['@type' => 'Organization', 'name' => $site['name']],
    ]), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endpush

@section('content')
    @include('frontend.partials.page-header', [
        'crumb' => __('স্বাস্থ্য পরামর্শ'),
        'eyebrow' => $article['category'] ?: __('স্বাস্থ্য পরামর্শ'),
        'title' => e($article['title']),
        'lead' => $article['excerpt'] ?: $article['date']->translatedFormat('d F Y'),
    ])

    <section class="section pt-0">
        <div class="container">
            <article class="article-page">
                @if ($article['image'])
                    <img class="article-cover" src="{{ $article['image'] }}" alt="" width="1280" height="720">
                @endif
                <p class="text-body-secondary small"><time datetime="{{ $article['date']->toDateString() }}">{{ $article['date']->translatedFormat('d F Y') }}</time></p>
                <div class="article-content">{!! $article['body'] !!}</div>
                <p class="article-note"><i class="bi bi-info-circle" aria-hidden="true"></i> {{ __('এই লেখা সাধারণ তথ্যের জন্য; চিকিৎসার সিদ্ধান্তের আগে আপনার চিকিৎসকের পরামর্শ নিন।') }}</p>
                <div class="d-flex flex-wrap gap-3 mt-4">
                    <a class="btn btn-primary" href="{{ route('site.appointment') }}"><i class="bi bi-calendar2-check" aria-hidden="true"></i> {{ __('অ্যাপয়েন্টমেন্ট নিন') }}</a>
                    <a class="btn btn-ghost" href="{{ route('site.articles') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> {{ __('সব স্বাস্থ্য পরামর্শ') }}</a>
                </div>
            </article>

            @if ($more)
                <h2 class="h4 mt-5 mb-4 text-center">{{ __('আরও পড়ুন') }}</h2>
                <div class="row g-4">
                    @foreach ($more as $item)
                        <div class="col-md-6 col-lg-4">@include('frontend.partials.article-card', ['article' => $item])</div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
