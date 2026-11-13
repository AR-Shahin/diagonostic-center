@php $popular = array_values(array_filter($content['tests'], fn ($t) => $t['popular'])); @endphp
<section class="section" aria-labelledby="tests-title">
    <div class="container">
        @include('frontend.partials.section-head', [
            'id' => 'tests-title',
            'eyebrow' => __('জনপ্রিয় টেস্ট'),
            'title' => __('সবচেয়ে বেশি করানো <span class="grad">ডায়াগনস্টিক টেস্ট</span>'),
            'sub' => __('মূল্য, রিপোর্ট ডেলিভারির সময় ও প্রস্তুতি—বুক করার আগেই সব জেনে নিন।'),
        ])

        <div class="row g-4">
            @foreach ($popular as $test)
                <div class="col-md-6 col-lg-4 reveal" style="--d:{{ ($loop->index % 3) * 0.07 }}s">
                    @include('frontend.partials.test-card', ['test' => $test, 'departments' => $content['departments']])
                </div>
            @endforeach
        </div>

        <div class="text-center mt-5 reveal">
            <a class="btn btn-ghost btn-lg" href="{{ route('site.tests') }}">{{ __('সব টেস্ট দেখুন') }} <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        </div>
    </div>
</section>
