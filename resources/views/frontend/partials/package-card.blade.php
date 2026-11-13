{{-- Props: $pkg, $limit (optional, how many tests to list) --}}
@php
    $limit = $limit ?? ($pkg['featured'] ? 7 : 4);
    $shown = array_slice($pkg['tests'], 0, $limit);
    $more = count($pkg['tests']) - count($shown);
@endphp
<article class="card-soft pkg-card {{ $pkg['featured'] ? 'is-featured' : '' }}" id="{{ $pkg['slug'] }}">
    @if ($pkg['featured'])
        <span class="pkg-badge"><i class="bi bi-stars" aria-hidden="true"></i> {{ __('জনপ্রিয়') }}</span>
    @endif
    <span class="icon-bubble"><i class="bi {{ $pkg['icon'] }}" aria-hidden="true"></i></span>
    <h3 class="pkg-name">{{ $pkg['bn'] }}</h3>
    <p class="pkg-en alt-lang" lang="en">{{ $pkg['en'] }}</p>
    <p class="pkg-tagline">{{ $pkg['tagline'] }}</p>

    <ul class="check-list">
        @foreach ($shown as $t)
            <li><i class="bi bi-check2-circle" aria-hidden="true"></i> {{ $t }}</li>
        @endforeach
        @if ($more > 0)
            <li class="more">{{ __('+ আরও :n টি টেস্ট', ['n' => $more]) }}</li>
        @endif
    </ul>

    <div class="pkg-foot">
        <div class="price">
            <s class="price-old">৳ {{ $pkg['old_price'] }}</s>
            <span class="price-now">৳ {{ $pkg['price'] }}</span>
            <span class="tag tag-teal">{{ __('সাশ্রয়') }} ৳ {{ $pkg['saving'] }}</span>
        </div>
        <div class="pkg-actions">
            <a href="{{ route('site.packages') }}#{{ $pkg['slug'] }}" class="btn btn-ghost btn-sm">{{ __('বিস্তারিত') }}</a>
            <a href="{{ route('site.appointment', ['package' => $pkg['slug']]) }}" class="btn btn-primary btn-sm">{{ __('বুক করুন') }}</a>
        </div>
    </div>
</article>
