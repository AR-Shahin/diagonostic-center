<section class="section section-alt" aria-labelledby="why-title">
    <div class="container">
        @include('frontend.partials.section-head', [
            'id' => 'why-title',
            'eyebrow' => 'কেন আমরা',
            'title' => 'আপনার আস্থা, <span class="grad">আমাদের অঙ্গীকার</span>',
            'sub' => 'নির্ভুল ফলাফল, স্বচ্ছ মূল্য এবং রোগীর প্রতি যত্ন—এই তিনটি আমাদের সেবার মূল ভিত্তি।',
        ])

        <div class="row g-4">
            @foreach ($content['why'] as $item)
                <div class="{{ $loop->index < 4 ? 'col-sm-6 col-lg-3' : 'col-md-4' }} reveal" style="--d:{{ ($loop->index % 4) * 0.06 }}s">
                    <div class="why-card">
                        <span class="icon-bubble"><i class="bi {{ $item['icon'] }}" aria-hidden="true"></i></span>
                        <h3>{{ $item['title'] }}</h3>
                        <p>{{ $item['desc'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
