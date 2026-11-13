@php
    $packages = $content['packages'];
    $featured = $packages[0];
    $others = array_slice($packages, 1);
@endphp
<section class="section section-alt" aria-labelledby="packages-title">
    <div class="container">
        @include('frontend.partials.section-head', [
            'id' => 'packages-title',
            'eyebrow' => 'হেলথ চেকআপ প্যাকেজ',
            'title' => 'প্রয়োজন বুঝে <span class="grad">সাশ্রয়ী প্যাকেজ</span>',
            'sub' => 'একসাথে একাধিক টেস্ট, কম খরচে। প্রতিটি প্যাকেজে কী কী টেস্ট আছে, আগেই দেখে নিন।',
        ])

        <div class="row g-4">
            <div class="col-lg-5 reveal">
                @include('frontend.partials.package-card', ['pkg' => $featured])
            </div>
            <div class="col-lg-7">
                <div class="row g-4">
                    @foreach ($others as $pkg)
                        <div class="col-md-6 reveal" style="--d:{{ $loop->index * 0.07 }}s">
                            @include('frontend.partials.package-card', ['pkg' => $pkg, 'limit' => 3])
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="text-center mt-5 reveal">
            <a class="btn btn-ghost btn-lg" href="{{ route('site.packages') }}">সব প্যাকেজ দেখুন <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        </div>
    </div>
</section>
