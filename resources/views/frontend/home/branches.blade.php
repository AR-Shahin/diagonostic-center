<section class="section section-alt" id="branches" aria-labelledby="branches-title">
    <div class="container">
        @include('frontend.partials.section-head', [
            'id' => 'branches-title',
            'eyebrow' => __('আমাদের শাখা'),
            'title' => __('আপনার <span class="grad">কাছের শাখা</span> খুঁজুন'),
            'sub' => __('ঠিকানা, খোলার সময় ও উপলব্ধ সেবা দেখে নিন।'),
        ])

        <div class="row g-4">
            @foreach ($content['branches'] as $branch)
                <div class="col-md-6 col-lg-4 reveal" style="--d:{{ $loop->index * 0.07 }}s">
                    <article class="card-soft branch-card">
                        <div class="branch-media">
                            @if ($branch['image'])
                                <img src="{{ $branch['image'] }}" alt="{{ $branch['name'] }}" width="640" height="360" loading="lazy">
                            @else
                                <i class="bi bi-hospital" aria-hidden="true"></i>
                            @endif
                        </div>
                        <div class="branch-body">
                            <h3>{{ $branch['name'] }}</h3>
                            <ul class="meta-list">
                                <li><i class="bi bi-geo-alt" aria-hidden="true"></i> {{ $branch['address'] }}</li>
                                <li><i class="bi bi-telephone" aria-hidden="true"></i> {{ $branch['phone'] }}</li>
                                <li><i class="bi bi-clock" aria-hidden="true"></i> {{ $branch['hours'] }}</li>
                            </ul>
                            <div class="chip-row chip-row-sm">
                                @foreach ($branch['services'] as $s)
                                    <span class="tag tag-blue">{{ $s }}</span>
                                @endforeach
                            </div>
                            <div class="branch-actions">
                                <a class="btn btn-primary btn-sm" href="{{ route('site.contact') }}">{{ __('শাখা খুঁজুন') }}</a>
                                <a class="btn btn-ghost btn-sm" href="{{ ($branch['map_url'] ?? null) ?: 'https://www.google.com/maps/search/?api=1&query='.urlencode($branch['address']) }}" target="_blank" rel="noopener"><i class="bi bi-sign-turn-right" aria-hidden="true"></i> {{ __('গুগল ম্যাপ') }}</a>
                            </div>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
    </div>
</section>
