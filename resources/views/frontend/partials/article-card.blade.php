{{-- Props: $article --}}
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
            @if ($article['category'])<span class="tag tag-blue">{{ $article['category'] }}</span>@endif
            <time datetime="{{ $article['date']->toDateString() }}">{{ $article['date']->translatedFormat('d M Y') }}</time>
        </div>
        <h3><a href="{{ $article['url'] }}">{{ $article['title'] }}</a></h3>
        @if ($article['excerpt'])<p>{{ $article['excerpt'] }}</p>@endif
        <a class="link-arrow" href="{{ $article['url'] }}">{{ __('আরও পড়ুন') }} <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
    </div>
</article>
