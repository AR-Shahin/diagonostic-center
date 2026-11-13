<section class="section section-alt" aria-labelledby="services-title">
    <div class="container">
        @include('frontend.partials.section-head', [
            'id' => 'services-title',
            'eyebrow' => 'আমাদের সেবা',
            'title' => 'রক্ত পরীক্ষা থেকে <span class="grad">আধুনিক ইমেজিং</span>',
            'sub' => 'প্রয়োজনীয় ডায়াগনস্টিক সেবা এক জায়গায়।',
        ])

        <div class="bento">
            @foreach ($content['services'] as $service)
                <a class="bento-tile tone-{{ $service['tone'] }} span-{{ $service['span'] }} reveal" style="--d:{{ ($loop->index % 3) * 0.06 }}s" href="{{ route('site.services') }}#{{ $service['slug'] }}">
                    <i class="bi {{ $service['icon'] }} bento-watermark" aria-hidden="true"></i>
                    <span class="icon-bubble"><i class="bi {{ $service['icon'] }}" aria-hidden="true"></i></span>
                    <div class="bento-text">
                        <h3>{{ $service['bn'] }}</h3>
                        <p lang="en" class="bento-en">{{ $service['en'] }}</p>
                        <p class="bento-desc">{{ $service['desc'] }}</p>
                    </div>
                    <span class="bento-link">বিস্তারিত <i class="bi bi-arrow-up-right" aria-hidden="true"></i></span>
                </a>
            @endforeach
        </div>
    </div>
</section>
