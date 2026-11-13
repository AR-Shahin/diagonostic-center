<section class="section pb-0" aria-labelledby="quick-title">
    <div class="container">
        <h2 class="visually-hidden" id="quick-title">{{ __('দ্রুত সেবা') }}</h2>
        <div class="row g-3 g-lg-4">
            @foreach ($content['quick'] as $item)
                <div class="col-sm-6 col-lg-3 reveal" style="--d:{{ $loop->index * 0.06 }}s">
                    <a class="quick-card" href="{{ $item['url'] }}">
                        <span class="icon-bubble"><i class="bi {{ $item['icon'] }}" aria-hidden="true"></i></span>
                        <span class="quick-text">
                            <strong>{{ $item['title'] }}</strong>
                            <small>{{ $item['desc'] }}</small>
                        </span>
                        <i class="bi bi-arrow-right quick-arrow" aria-hidden="true"></i>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>
