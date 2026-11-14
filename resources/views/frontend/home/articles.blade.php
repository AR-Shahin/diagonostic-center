<section class="section" id="articles" aria-labelledby="articles-title">
    <div class="container">
        @include('frontend.partials.section-head', [
            'id' => 'articles-title',
            'eyebrow' => __('স্বাস্থ্য পরামর্শ ও নিবন্ধ'),
            'title' => __('সুস্থ থাকতে <span class="grad">জানুন আরও</span>'),
            'sub' => __('স্বাস্থ্য সচেতনতা, টেস্টের প্রস্তুতি ও মৌসুমি পরামর্শ।'),
        ])

        <div class="row g-4">
            @foreach ($content['articles'] as $article)
                <div class="col-md-6 col-lg-4 reveal" style="--d:{{ $loop->index * 0.07 }}s">
                    @include('frontend.partials.article-card', ['article' => $article])
                </div>
            @endforeach
        </div>

        <div class="text-center mt-5 reveal">
            <a class="btn btn-ghost btn-lg" href="{{ route('site.articles') }}">{{ __('সব স্বাস্থ্য পরামর্শ') }} <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        </div>
    </div>
</section>
