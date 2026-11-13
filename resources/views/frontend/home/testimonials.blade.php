<section class="section" aria-labelledby="reviews-title">
    <div class="container">
        @include('frontend.partials.section-head', [
            'id' => 'reviews-title',
            'eyebrow' => __('রোগীদের মতামত'),
            'title' => __('যাঁরা আমাদের <span class="grad">সেবা নিয়েছেন</span>'),
            'sub' => __('সম্মতিসহ প্রকাশিত রোগীদের অভিজ্ঞতা।'),
        ])

        <div class="row g-4">
            @foreach ($content['testimonials'] as $review)
                <div class="col-md-6 col-lg-4 reveal" style="--d:{{ $loop->index * 0.07 }}s">
                    <figure class="card-soft review-card">
                        <i class="bi bi-quote review-quote" aria-hidden="true"></i>
                        <div class="stars" role="img" aria-label="{{ __(':n তারকা', ['n' => $review['rating']]) }}">
                            @for ($i = 1; $i <= 5; $i++)
                                <i class="bi {{ $i <= $review['rating'] ? 'bi-star-fill' : 'bi-star' }}" aria-hidden="true"></i>
                            @endfor
                        </div>
                        <blockquote>{{ $review['text'] }}</blockquote>
                        <figcaption>
                            <span class="avatar-sm" aria-hidden="true"><i class="bi bi-person-fill"></i></span>
                            <span><b>{{ $review['name'] }}</b><small>{{ $review['meta'] }}</small></span>
                        </figcaption>
                    </figure>
                </div>
            @endforeach
        </div>

        {{-- Google Reviews: connect the Places API / embed here when available --}}
        <p class="text-center mt-4 mb-0 reveal"><a class="link-arrow" href="#">{{ __('গুগল রিভিউ দেখুন') }} <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i></a></p>
    </div>
</section>
