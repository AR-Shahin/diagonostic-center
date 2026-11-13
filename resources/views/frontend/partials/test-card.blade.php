{{-- Props: $test, $departments --}}
<article class="card-soft test-card" data-filter-item data-cat="{{ $test['cat'] }}" data-name="{{ mb_strtolower($test['name'].' '.$test['bn']) }}">
    <div class="test-card-top">
        <span class="tag tag-blue">{{ $departments[$test['cat']]['bn'] }}</span>
        @if ($test['discount'])
            <span class="tag tag-teal"><i class="bi bi-tag-fill" aria-hidden="true"></i> {{ __('ছাড়') }}</span>
        @endif
    </div>

    <h3 class="test-name">{{ $test['name'] }}</h3>
    <p class="test-bn alt-lang">{{ $test['bn'] }}</p>

    <ul class="meta-list">
        <li><i class="bi bi-clock" aria-hidden="true"></i> {{ __('রিপোর্ট:') }} {{ $test['report'] }}</li>
        <li><i class="bi bi-info-circle" aria-hidden="true"></i> {{ $test['prep'] }}</li>
    </ul>

    <div class="test-card-foot">
        <div class="price">
            @if ($test['discount'])
                <s class="price-old">৳ {{ $test['price'] }}</s>
                <span class="price-now">৳ {{ $test['discount'] }}</span>
            @else
                <span class="price-now">৳ {{ $test['price'] }}</span>
            @endif
        </div>
        <a href="{{ route('site.appointment', ['test' => $test['slug']]) }}" class="btn btn-primary btn-sm">{{ __('বুক করুন') }}</a>
    </div>
</article>
