<section class="section" aria-labelledby="doctors-title">
    <div class="container">
        @include('frontend.partials.section-head', [
            'id' => 'doctors-title',
            'eyebrow' => __('বিশেষজ্ঞ চিকিৎসক'),
            'title' => __('অভিজ্ঞ চিকিৎসকদের <span class="grad">পরামর্শ নিন</span>'),
            'sub' => __('চেম্বারের সময় দেখে সহজেই অ্যাপয়েন্টমেন্ট বুক করুন।'),
        ])

        <div class="row g-4">
            @foreach (array_slice($content['doctors'], 0, 4) as $doctor)
                <div class="col-sm-6 col-lg-3 reveal" style="--d:{{ $loop->index * 0.07 }}s">
                    @include('frontend.partials.doctor-card', ['doctor' => $doctor])
                </div>
            @endforeach
        </div>

        <div class="text-center mt-5 reveal">
            <a class="btn btn-ghost btn-lg" href="{{ route('site.doctors') }}">{{ __('সব চিকিৎসক দেখুন') }} <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        </div>
    </div>
</section>
