<section class="section" aria-labelledby="dept-title">
    <div class="container">
        @include('frontend.partials.section-head', [
            'id' => 'dept-title',
            'eyebrow' => 'ডায়াগনস্টিক বিভাগ',
            'title' => 'প্রতিটি পরীক্ষার জন্য <span class="grad">আলাদা বিভাগ</span>',
            'sub' => 'বিভাগ বেছে নিন, কী কী সেবা পাওয়া যায় দেখুন।',
        ])

        <div class="row g-4 g-lg-5 align-items-start dept reveal">
            <div class="col-lg-4">
                <div class="dept-nav" role="tablist" aria-orientation="vertical" aria-label="ডায়াগনস্টিক বিভাগ">
                    @foreach ($content['departments'] as $slug => $d)
                        <button class="dept-tab {{ $loop->first ? 'active' : '' }}" id="dept-{{ $slug }}-tab" type="button" role="tab"
                                data-bs-toggle="pill" data-bs-target="#dept-{{ $slug }}" aria-controls="dept-{{ $slug }}"
                                aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                            <span class="icon-bubble icon-sm"><i class="bi {{ $d['icon'] }}" aria-hidden="true"></i></span>
                            <span class="dept-tab-text">{{ $d['bn'] }}<small lang="en">{{ $d['en'] }}</small></span>
                            <i class="bi bi-chevron-right dept-chevron" aria-hidden="true"></i>
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="col-lg-8">
                <div class="tab-content">
                    @foreach ($content['departments'] as $slug => $d)
                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="dept-{{ $slug }}" role="tabpanel" aria-labelledby="dept-{{ $slug }}-tab" tabindex="0">
                            <div class="dept-panel">
                                <i class="bi {{ $d['icon'] }} dept-watermark" aria-hidden="true"></i>
                                <span class="tag tag-blue">{{ $d['en'] }}</span>
                                <h3 class="dept-title">{{ $d['bn'] }}</h3>
                                <p class="dept-desc">{{ $d['desc'] }}</p>
                                <ul class="check-list check-list-lg">
                                    @foreach ($d['services'] as $service)
                                        <li><i class="bi bi-check2-circle" aria-hidden="true"></i> {{ $service }}</li>
                                    @endforeach
                                </ul>
                                <a class="btn btn-primary" href="{{ route('site.tests', ['cat' => $slug]) }}">এই বিভাগের টেস্ট দেখুন <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
