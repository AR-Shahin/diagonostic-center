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
                    <article class="card-soft article-card">
                        <a class="article-media" href="{{ $article['url'] }}" tabindex="-1" aria-hidden="true">
                            @if ($article['image'])
                                <img src="{{ $article['image'] }}" alt="" width="640" height="360" loading="lazy">
                            @else
                                <i class="bi bi-journal-medical"></i>
                            @endif
                        </a>
                        <div class="article-body">
                            <div class="article-meta">
                                <span class="tag tag-blue">{{ $article['category'] }}</span>
                                <time datetime="{{ $article['date']->toDateString() }}">{{ $article['date']->format('d M Y') }}</time>
                            </div>
                            <h3><a href="{{ $article['url'] }}">{{ $article['title'] }}</a></h3>
                            <p>{{ $article['excerpt'] }}</p>
                            <a class="link-arrow" href="{{ $article['url'] }}">{{ __('আরও পড়ুন') }} <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
    </div>
</section>
